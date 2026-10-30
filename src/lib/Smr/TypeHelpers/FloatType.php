<?php declare(strict_types=1);

namespace Smr\TypeHelpers;

/**
 * @extends Type<float>
 */
final class FloatType extends Type {

	public function accepts(mixed $value): bool {
		return is_float($value);
	}

	public function name(): string {
		return 'float';
	}

}
