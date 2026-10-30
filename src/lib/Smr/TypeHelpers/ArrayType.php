<?php declare(strict_types=1);

namespace Smr\TypeHelpers;

/**
 * @extends Type<array<mixed>>
 */
final class ArrayType extends Type {

	public function accepts(mixed $value): bool {
		return is_array($value);
	}

	public function name(): string {
		return 'array';
	}

}
