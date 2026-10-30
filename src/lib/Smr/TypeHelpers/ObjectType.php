<?php declare(strict_types=1);

namespace Smr\TypeHelpers;

/**
 * @template T of object
 * @extends Type<T>
 */
final class ObjectType extends Type {

	/**
	 * @param class-string<T> $class
	 */
	public function __construct(private readonly string $class) {
	}

	public function accepts(mixed $value): bool {
		return $value instanceof $this->class;
	}

	public function name(): string {
		return $this->class;
	}

}
