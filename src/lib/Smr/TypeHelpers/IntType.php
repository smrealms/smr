<?php declare(strict_types=1);

namespace Smr\TypeHelpers;

/**
 * @extends Type<int>
 */
final class IntType extends Type {

	public function accepts(mixed $value): bool {
		return is_int($value);
	}

	public function name(): string {
		return 'int';
	}

}
