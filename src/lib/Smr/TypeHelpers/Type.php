<?php declare(strict_types=1);

namespace Smr\TypeHelpers;

/**
 * A runtime type check for values of type T.
 *
 * @template-covariant T
 */
abstract class Type {

	/**
	 * @template TKey of array-key = array-key
	 * @template TValue = mixed
	 * @param ?Type<TValue> $valueType
	 * @param ?Type<TKey> $keyType
	 * @return ArrayType<TKey, TValue>
	 */
	public static function array(?Type $valueType = null, ?Type $keyType = null): ArrayType {
		return new ArrayType(valueType: $valueType, keyType: $keyType);
	}

	public static function string(): StringType {
		return new StringType();
	}

	public static function int(): IntType {
		return new IntType();
	}

	public static function float(): FloatType {
		return new FloatType();
	}

	public static function bool(): BoolType {
		return new BoolType();
	}

	/**
	 * @template TObject of object
	 * @param class-string<TObject> $class
	 * @return ObjectType<TObject>
	 */
	public static function object(string $class): ObjectType {
		return new ObjectType($class);
	}

	/**
	 * @phpstan-assert-if-true T $value
	 */
	abstract public function accepts(mixed $value): bool;

	abstract public function name(): string;

}
