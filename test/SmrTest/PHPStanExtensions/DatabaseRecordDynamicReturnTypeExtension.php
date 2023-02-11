<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensions;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use Smr\DatabaseRecord;

/**
 * Narrows DatabaseRecord getter return types from an inferred row shape.
 *
 * DatabaseRecordTypeResolver contains the shared validation and inference.
 * When inference finds an invalid field access, this adapter falls back to the
 * declared getter type. DatabaseRecordRule reports the precise diagnostic.
 */
class DatabaseRecordDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension {

	public function getClass(): string {
		return DatabaseRecord::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool {
		return DatabaseRecordTypeResolver::isMethodSupported($methodReflection);
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope,
	): ?Type {
		try {
			return DatabaseRecordTypeResolver::resolve($methodReflection, $methodCall, $scope);
		} catch (DatabaseRecordTypeException) {
			return CallArgumentResolver::resolve(
				$methodReflection,
				$methodCall,
				$scope,
			)->getParametersAcceptor()->getReturnType();
		}
	}

}
