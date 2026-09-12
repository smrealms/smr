<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;
use staabm\PHPStanDba\QueryReflection\QueryReflection;
use function array_key_exists;
use function count;
use function is_string;

/**
	* Validates configured Database API calls whose array values name columns.
	*
	* phpstan-dba's DoctrineKeyValueStyleRule validates arrays keyed by column
	* name. SMR's select() API instead supplies column names as array values.
	* This rule resolves the configured table against phpstan-dba's schema and
	* reports dynamic or invalid table, array, and column values.
	*
	* @implements Rule<CallLike>
 */
final class ArrayValuesAreColumnsRule implements Rule {

	/**
	 * @var array<array{string, string, list<int>}>
	 */
	public array $classMethods;

	private ?QueryReflection $queryReflection = null;

	/**
	 * @param list<string> $classMethods
	 */
	public function __construct(
		array $classMethods,
		private readonly ReflectionProvider $reflectionProvider,
	) {
		$this->classMethods = [];
		foreach ($classMethods as $classMethod) {
			sscanf($classMethod, '%[^::]::%[^#]#%[0-9,]', $className, $methodName, $arrayArgPositions);
			if (!is_string($className) || !is_string($methodName)) {
				throw new ShouldNotHappenException('Invalid classMethod definition');
			}
			if ($arrayArgPositions !== null) {
				$arrayArgPositions = array_map(
					fn($pos) => (int)$pos,
					explode(',', (string)$arrayArgPositions),
				);
			} else {
				$arrayArgPositions = [];
			}
			$this->classMethods[] = [$className, $methodName, $arrayArgPositions];
		}
	}

	public function getNodeType(): string {
		return CallLike::class;
	}

	/**
	 * @return list<\PHPStan\Rules\IdentifierRuleError>
	 */
	public function processNode(Node $callLike, Scope $scope): array {
		if ($callLike instanceof MethodCall) {
			if (!$callLike->name instanceof Identifier) {
				return [];
			}

			$methodReflection = $scope->getMethodReflection($scope->getType($callLike->var), $callLike->name->toString());
		} elseif ($callLike instanceof New_) {
			if (!$callLike->class instanceof FullyQualified) {
				return [];
			}
			$methodReflection = $scope->getMethodReflection(new ObjectType($callLike->class->toCodeString()), '__construct');
		} else {
			return [];
		}

		if ($methodReflection === null) {
			return [];
		}

		$unsupportedMethod = true;
		$arrayArgPositions = [];
		foreach ($this->classMethods as [$className, $methodName, $arrayArgPositionsConfig]) {
			if ($methodName === $methodReflection->getName() &&
				(
					$methodReflection->getDeclaringClass()->getName() === $className
					|| ($this->reflectionProvider->hasClass($className) && $methodReflection->getDeclaringClass()->isSubclassOfClass($this->reflectionProvider->getClass($className)))
				)
			) {
				$arrayArgPositions = $arrayArgPositionsConfig;
				$unsupportedMethod = false;
				break;
			}
		}

		if ($unsupportedMethod) {
			return [];
		}

		if (count($callLike->getArgs()) < 1) {
			return [];
		}

		$resolvedArguments = CallArgumentResolver::resolve($methodReflection, $callLike, $scope);
		$paramIndexToArg = $resolvedArguments->getArguments();
		if ($paramIndexToArg === null) {
			return [];
		}

		if (!array_key_exists(0, $paramIndexToArg)) {
			return [];
		}
		$parameters = $resolvedArguments->getParametersAcceptor()->getParameters();

		$tableExpr = $paramIndexToArg[0]->value;
		$tableType = $scope->getType($tableExpr);
		$tableNames = $tableType->getConstantStrings();
		if (count($tableNames) === 0) {
			return [
				RuleErrorBuilder::message('Argument #0 expects a constant string, got ' . $tableType->describe(VerbosityLevel::precise()))->identifier('dba.ArrayValuesAreColumnsRule')->line($callLike->getStartLine())->build(),
			];
		}

		if ($this->queryReflection === null) {
			$this->queryReflection = new QueryReflection();
		}
		$schemaReflection = $this->queryReflection->getSchemaReflection();

		$errors = [];
		foreach ($tableNames as $tableName) {
			// Table name may be escaped with backticks
			$tableName = trim($tableName->getValue(), '`');
			$table = $schemaReflection->getTable($tableName);
			if ($table === null) {
				$errors[] = 'Table "' . $tableName . '" does not exist';
				continue;
			}

			// All array arguments should have table columns as VALUES
			// (whereas DoctrineKeyValueStyleRule has table columns as KEYS)
			foreach ($arrayArgPositions as $arrayArgPosition) {
				// If the argument doesn't exist, just skip it since we don't want
				// to error in case it has a default value
				if (!array_key_exists($arrayArgPosition, $paramIndexToArg)) {
					continue;
				}
				$isReturnColumnsArgument = isset($parameters[$arrayArgPosition])
					&& $parameters[$arrayArgPosition]->getName() === 'returnColumns';

				$argType = $scope->getType($paramIndexToArg[$arrayArgPosition]->value);
				$argArrays = $argType->getConstantArrays();
				if (count($argArrays) === 0) {
					$errors[] = 'Argument #' . $arrayArgPosition . ' is not a constant array, got ' . $argType->describe(VerbosityLevel::precise());
					continue;
				}

				foreach ($argArrays as $argArray) {
					foreach ($argArray->getValueTypes() as $valueIndex => $valueType) {
						$valueNames = $valueType->getConstantStrings();
						if (count($valueNames) === 0) {
							$errors[] = 'Element #' . $valueIndex . ' of argument #' . $arrayArgPosition . ' must have a string value, got ' . $valueType->describe(VerbosityLevel::precise());
							continue;
						}

						foreach ($valueNames as $valueName) {
							// Column name may be escaped with backticks
							$argColumnName = trim($valueName->getValue(), '`');
							if ($isReturnColumnsArgument && $argColumnName === '*') {
								// select() accepts '*' to request every table column.
								continue;
							}

							$argColumn = null;
							foreach ($table->getColumns() as $column) {
								if ($argColumnName === $column->getName()) {
									$argColumn = $column;
								}
							}
							if ($argColumn === null) {
								$errors[] = 'Column "' . $table->getName() . '.' . $argColumnName . '" does not exist';
								continue;
							}
						}
					}
				}
			}
		}

		$ruleErrors = [];
		foreach ($errors as $error) {
			$ruleErrors[] = RuleErrorBuilder::message('Query error: ' . $error)->identifier('dba.ArrayValuesAreColumnsRule')->line($callLike->getStartLine())->build();
		}
		return $ruleErrors;
	}

}
