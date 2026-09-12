<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use Smr\Database;
use staabm\PHPStanDba\DoctrineReflection\DoctrineResultObjectType;
use staabm\PHPStanDba\QueryReflection\QueryReflection;
use staabm\PHPStanDba\QueryReflection\QueryReflector;

/**
 * Infers Database::select() result rows from a constant table and columns.
 *
 * select() is a table-oriented wrapper rather than raw SQL. This extension
 * builds an equivalent SELECT for phpstan-dba, then wraps the inferred row in
 * DatabaseResultObjectType. Dynamic inputs fall back to the declared type.
 */
class DatabaseSelectDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension {

	public function getClass(): string {
		return Database::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool {
		return $methodReflection->getName() === 'select';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope,
	): ?Type {
		// Get the table and result columns from this call.
		$resolvedArguments = CallArgumentResolver::resolve($methodReflection, $methodCall, $scope);
		$paramIndexToArg = $resolvedArguments->getArguments();
		if ($paramIndexToArg === null) {
			return null;
		}
		$params = $resolvedArguments->getParametersAcceptor()->getParameters();

		if (!isset($paramIndexToArg[0])) {
			return null;
		}

		$table = $this->resolveString($scope->getType($paramIndexToArg[0]->value));
		if ($table === null) {
			return null; // table is not a constant string
		}
		if (isset($paramIndexToArg[2])) {
			$resultColsType = $scope->getType($paramIndexToArg[2]->value);
		} else {
			// If argument not specified, get the type of the default value
			// (which should be ['*']).
			$resultColsType = $params[2]->getDefaultValue();
		}
		if ($resultColsType === null) {
			// Result columns should always have a type
			throw new ShouldNotHappenException();
		}
		$resultCols = $this->resolveStringArrayValues($resultColsType);
		if ($resultCols === null) {
			// Dynamic result columns cannot produce a static result shape.
			return null;
		}

		// Generate a proxy query with the same return type as the actual query
		// executed at runtime. We only need the table and result columns.
		$query = 'SELECT ' . implode(',', $resultCols) . ' FROM ' . $table;

		// Simulate the query and package it in the relevant result wrapper types
		// Note: Rather than simulating a query, we could have looked up the SQL
		// types for these columns and then converted them to PHP types, but it
		// wasn't obvious how to do that.
		$queryReflection = new QueryReflection();
		$resultType = $queryReflection->getResultType($query, QueryReflector::FETCH_TYPE_BOTH);
		if ($resultType === null) {
			return null; // failed to simulate query, hopefully will be caught by Rules
		}
		$doctrineResultType = DoctrineResultObjectType::newWithRowType($resultType);
		return new DatabaseResultObjectType($doctrineResultType);
	}

	/**
	 * @return ?non-empty-list<string>
	 */
	private function resolveStringArrayValues(Type $type): ?array {
		// iterate over all constant array possibilities (union-safe)
		$strings = [];
		foreach ($type->getConstantArrays() as $array) {
			foreach ($array->getValueTypes() as $valueType) {
				// each valueType might be a constant string
				foreach ($valueType->getConstantStrings() as $constString) {
					$strings[] = $constString->getValue();
				}
			}
		}
		return $strings ?: null;
	}

	private function resolveString(Type $type): ?string {
		// get all constant strings for this type (union-safe)
		$constantStrings = $type->getConstantStrings();

		if (count($constantStrings) !== 1) {
			return null; // not a single constant string, fallback
		}

		return $constantStrings[0]->getValue();
	}

}
