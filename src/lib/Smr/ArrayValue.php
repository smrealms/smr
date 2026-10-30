<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\TypeHelpers\Type;

/**
 * Get a value from an array given a key. If the array key exists, throws if it
 * is not the expected type. Can throw, return null, or return a default if the
 * array key does not exist.
 */
final class ArrayValue {

	/**
	 * @template T
	 * @param array<array-key, mixed> $arr
	 * @param Type<T> $type
	 * @return ?T
	 */
	public static function getOptional(array $arr, string|int $key, Type $type): mixed {
		if (!array_key_exists($key, $arr)) {
			return null;
		}
		$value = $arr[$key];
		if (!$type->accepts($value)) {
			throw new Exception('Array key [' . $key . '] must be a ' . $type->name() . ', got ' . get_debug_type($value) . ': ' . var_export($value, true));
		}
		return $value;
	}

	/**
	 * @template T
	 * @param array<array-key, mixed> $arr
	 * @param Type<T> $type
	 * @param T $default
	 * @return T
	 */
	public static function getOrDefault(array $arr, string|int $key, Type $type, mixed $default): mixed {
		return self::getOptional($arr, $key, $type) ?? $default;
	}

	/**
	 * @template T
	 * @param array<array-key, mixed> $arr
	 * @param Type<T> $type
	 * @return T
	 */
	public static function get(array $arr, string|int $key, Type $type): mixed {
		$value = self::getOptional($arr, $key, $type);
		if ($value === null) {
			throw new Exception('Could not find expected key in array: ' . $key);
		}
		return $value;
	}

}
