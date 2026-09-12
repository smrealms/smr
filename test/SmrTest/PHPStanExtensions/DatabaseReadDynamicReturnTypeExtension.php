<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use Smr\Database;
use staabm\PHPStanDba\Extensions\DoctrineConnectionExecuteQueryDynamicReturnTypeExtension;

/**
 * Adapts phpstan-dba's Doctrine query inference for Database::read().
 *
 * The proxy infers the underlying Doctrine result type. This extension wraps
 * that type in DatabaseResultObjectType so later SMR-specific extensions can
 * infer record() and records() field shapes.
 */
class DatabaseReadDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension {

	public function getClass(): string {
		return Database::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool {
		return $methodReflection->getName() === 'read';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope,
	): ?Type {
		$proxy = new DoctrineConnectionExecuteQueryDynamicReturnTypeExtension();
		$type = $proxy->getTypeFromMethodCall($methodReflection, $methodCall, $scope);
		if ($type === null) {
			// The query is not resolvable, so we cannot reason further about
			// the result of the query.
			return null;
		}
		return new DatabaseResultObjectType($type);
	}

}
