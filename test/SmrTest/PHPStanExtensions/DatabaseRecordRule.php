<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use Smr\DatabaseRecord;

/**
	* Reports DatabaseRecord accesses that cannot safely retain a precise type.
	*
	* The return-type extension must fall back to declared types when a field is
	* dynamic or incompatible with the inferred row. This Rule makes that loss
	* of precision visible, using DatabaseRecordTypeResolver for consistency.
	*
	* @implements Rule<MethodCall>
 */
class DatabaseRecordRule implements Rule {

	public function getNodeType(): string {
		return MethodCall::class;
	}

	/**
	 * @return list<\PHPStan\Rules\IdentifierRuleError> List of error messages from this node
	 */
	public function processNode(Node $methodCall, Scope $scope): array {
		if (!($methodCall->name instanceof Identifier)) {
			// PHPStan mostly ignores methods that are variables, favoring
			// instead first-class callables. We could grab the constant
			// strings from the type of the method name, but probably better
			// to stay in line with PHPStan convention here.
			return [];
		}

		$methodReflection = $scope->getMethodReflection(
			$scope->getType($methodCall->var),
			$methodCall->name->toString(),
		);
		if ($methodReflection?->getDeclaringClass()->getName() !== DatabaseRecord::class) {
			return [];
		}

		if (!($scope->getType($methodCall->var) instanceof DatabaseRecordObjectType)) {
			return [];
		}

		$resolvedArguments = CallArgumentResolver::resolve($methodReflection, $methodCall, $scope);
		$arguments = $resolvedArguments->getArguments();
		if ($arguments === null) {
			return [];
		}
		if (!isset($arguments[0])) {
			return [];
		}
		$fieldType = $scope->getType($arguments[0]->value);
		if (count($fieldType->getConstantStrings()) === 0) {
			return [
				RuleErrorBuilder::message(
					'Smr\\DatabaseRecord field name must be a constant string, got '
					. $fieldType->describe(VerbosityLevel::precise()),
				)->identifier('smr.dba.databaseRecordFieldName')->build(),
			];
		}

		try {
			DatabaseRecordTypeResolver::resolve(
				$methodReflection,
				$methodCall,
				$scope,
			);
		} catch (DatabaseRecordTypeException $err) {
			return [
				RuleErrorBuilder::message($err->getMessage())
					->identifier('smr.dba.databaseRecordType')
					->build(),
			];
		}

		return [];
	}

}
