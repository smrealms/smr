<?php declare(strict_types=1);

namespace Smr\TypeHelpers;

/**
 * @extends Type<bool>
 */
final class BoolType extends Type {

	public function accepts(mixed $value): bool {
		return is_bool($value);
	}

	public function name(): string {
		return 'bool';
	}

}
