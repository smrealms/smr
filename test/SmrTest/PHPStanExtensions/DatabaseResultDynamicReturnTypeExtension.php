<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use Generator;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use Smr\DatabaseResult;
use staabm\PHPStanDba\DoctrineReflection\DoctrineReflection;

/**
 * Propagates an inferred query row shape through DatabaseResult accessors.
 *
 * It converts Doctrine's fetch result into DatabaseRecordObjectType for
 * record(), and a Generator of that type for records(). Unresolvable query
 * results deliberately retain their declared DatabaseResult type instead.
 */
class DatabaseResultDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension {

	public function getClass(): string {
		return DatabaseResult::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool {
		return in_array($methodReflection->getName(), ['record', 'records'], true);
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope,
	): ?Type {
		$objectType = $scope->getType($methodCall->var);
		if (!($objectType instanceof DatabaseResultObjectType)) {
			// We have a DatabaseResult, but it either did not come directly
			// from a query, or the query was not resolvable. Therefore, we
			// won't know it's row type and cannot proceed.
			return null;
		}
		$innerType = $objectType->getRowType();

		// DatabaseResult internally calls Doctrine methods
		$proxyMethodReflection = $scope->getMethodReflection($innerType, 'fetchAssociative');
		if ($proxyMethodReflection === null) {
			throw new ShouldNotHappenException();
		}
		$doctrineReflection = new DoctrineReflection();
		$innerResultType = $doctrineReflection->reduceResultType($proxyMethodReflection, $innerType);
		if ($innerResultType === null) {
			throw new ShouldNotHappenException();
		}

		// Doctrine can return false on no rows, but we throw, so remove false
		$innerResultType = $innerResultType->tryRemove(new ConstantBooleanType(false));
		if ($innerResultType === null) {
			throw new ShouldNotHappenException();
		}

		$resultType = new DatabaseRecordObjectType($innerResultType);
		return match ($methodReflection->getName()) {
			'record' => $resultType,
			'records' => new GenericObjectType(Generator::class, [new IntegerType(), $resultType]),
			default => throw new ShouldNotHappenException(),
		};
	}

}
