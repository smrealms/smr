<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VerbosityLevel;

/**
 * Resolves DatabaseRecord getter types against the row shape from a query.
 *
 * This is deliberately independent of PHPStan extension interfaces so the
 * RTE and Rule share exactly the same field checks, numeric-string handling,
 * and fallback decisions.
 */
final class DatabaseRecordTypeResolver {

	private const array SKIP_TYPE_METHODS = [
		'getBoolean', // bools are stored in database as (enum) strings
		'getClass', // uses getObject
		'getNullableObject', // uses getObject
		'getObject', // objects are stored in the database as (serialized class) strings
		'getStringEnum', // string enums are stored in the database as strings
	];

	private const array INT_TYPE_METHODS = [
		'getInt',
		'getNullableInt',
	];

	/**
	 * @throws DatabaseRecordTypeException
	 */
	public static function resolve(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope,
	): ?Type {
		$objectType = $scope->getType($methodCall->var);
		if (!($objectType instanceof DatabaseRecordObjectType)) {
			// We have a DatabaseRecord, but it either did not come directly
			// from a query, or the query was not resolvable. Therefore, we
			// won't know its row type and cannot proceed.
			return null;
		}
		if ($methodReflection->getName() === 'getRow') {
			return $objectType->getRowType();
		}

		$resolvedArguments = CallArgumentResolver::resolve($methodReflection, $methodCall, $scope);
		$returnType = $resolvedArguments->getParametersAcceptor()->getReturnType();
		$arguments = $resolvedArguments->getArguments();
		if ($arguments === null) {
			return $returnType;
		}
		if (!isset($arguments[0])) {
			return $returnType;
		}

		// We should always have a constant array inside the DatabaseRecord.
		$constantArrays = $objectType->getRowType()->getConstantArrays();
		if (count($constantArrays) !== 1) {
			throw new ShouldNotHappenException();
		}
		$rowType = $constantArrays[0];

		$dbFieldNames = [];
		foreach ($rowType->getKeyTypes() as $keyType) {
			$dbFieldNames[] = $keyType->getConstantStrings()[0]->getValue();
		}

		$fieldType = $scope->getType($arguments[0]->value);
		$fieldNames = array_map(
			fn(ConstantStringType $type) => $type->getValue(),
			$fieldType->getConstantStrings(),
		);
		if (count($fieldNames) === 0) {
			// No constant strings means the field cannot be checked against the
			// database record. The accompanying Rule reports this fallback.
			return $returnType;
		}

		$returnTypes = [];
		foreach ($fieldNames as $fieldName) {
			$matchingIndex = array_search($fieldName, $dbFieldNames, true);
			if ($matchingIndex === false) {
				throw new DatabaseRecordTypeException(
					$methodReflection->getDeclaringClass()->getName() . ' does not contain field: ' . $fieldName,
				);
			}

			$methodName = $methodReflection->getName();
			if (in_array($methodName, self::SKIP_TYPE_METHODS, true)) {
				continue;
			}

			$databaseType = $rowType->getValueTypes()[$matchingIndex];
			if (in_array($methodName, self::INT_TYPE_METHODS, true) && $databaseType->isNumericString()->yes()) {
				// getInt() and getNullableInt() convert numeric strings to ints.
				$returnTypes[] = $databaseType->toInteger();
				continue;
			}
			if (!$returnType->isSuperTypeOf($databaseType)->yes()) {
				$verbosity = VerbosityLevel::precise();
				throw new DatabaseRecordTypeException(
					$methodReflection->getDeclaringClass()->getName() . '::' . $methodName
					. ' returns type ' . $returnType->describe($verbosity) . ', but field `' . $fieldName . '`'
					. ' has type ' . $databaseType->describe($verbosity),
				);
			}
			$returnTypes[] = $databaseType;
		}

		return $returnTypes === [] ? $returnType : TypeCombinator::union(...$returnTypes);
	}

}
