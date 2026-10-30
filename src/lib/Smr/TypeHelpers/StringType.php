<?php declare(strict_types=1);

namespace Smr\TypeHelpers;

/**
 * @extends Type<string>
 */
final class StringType extends Type {

	public function accepts(mixed $value): bool {
		return is_string($value);
	}

	public function name(): string {
		return 'string';
	}

}
