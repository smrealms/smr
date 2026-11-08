<?php declare(strict_types=1);

namespace Smr\TypeHelpers;

/**
 * @template-covariant TKey of array-key = array-key
 * @template-covariant TValue = mixed
 * @extends Type<array<TKey, TValue>>
 */
final class ArrayType extends Type {

	/**
	 * @param ?Type<TValue> $valueType
	 * @param ?Type<TKey> $keyType
	 */
	public function __construct(
		private readonly ?Type $valueType = null,
		private readonly ?Type $keyType = null,
	) {}

	public function accepts(mixed $value): bool {
		if (!is_array($value)) {
			return false;
		}
		return array_all(
			$value,
			fn($item, $key) => !(
				$this->keyType !== null && !$this->keyType->accepts($key)
				|| $this->valueType !== null && !$this->valueType->accepts($item)
			),
		);
	}

	public function name(): string {
		if ($this->keyType === null && $this->valueType === null) {
			return 'array';
		}
		$keyTypeName = $this->keyType?->name() ?? 'array-key';
		$valueTypeName = $this->valueType?->name() ?? 'mixed';
		return 'array<' . $keyTypeName . ', ' . $valueTypeName . '>';
	}

}
