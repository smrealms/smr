<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\TypeHelpers\Type;

/**
 * Should be used for getting request data for processing pages.
 * For display pages, see Smr\Session::getRequestVar.
 */
class Request {

	/**
	 * Returns true if index is set.
	 * Note that this must be used for checkboxes, since the element is not
	 * posted if a box is unchecked.
	 */
	public static function has(string $index): bool {
		return isset($_REQUEST[$index]);
	}

	/**
	 * @template T of bool|int|float
	 * @param ?T $default
	 * @param callable(string): ?T $filter
	 * @return T
	 */
	private static function getX(string $index, mixed $default, callable $filter): mixed {
		// Treat the empty string the same as missing
		$value = self::get($index, '');
		if ($value === '') {
			if ($default !== null) {
				return $default;
			}
			throw new Exception('No request variable or empty string for index "' . $index . '"');
		}
		$value = $filter($value);
		if ($value === null) {
			throw new Exception(
				'Value has the wrong type for index `' . $index . '`: '
				. var_export($_REQUEST[$index], true),
			);
		}
		return $value;
	}

	/**
	 * Returns index value as a boolean for boolean-like inputs.
	 * Whitespace is treated as missing: it returns the default or raises the same
	 * exception as an absent value, unlike has().
	 */
	public static function getBool(string $index, ?bool $default = null): bool {
		return self::getX(
			index: $index,
			default: $default,
			filter: static fn($value) => filter_var(
				$value,
				FILTER_VALIDATE_BOOL,
				FILTER_NULL_ON_FAILURE,
			),
		);
	}

	/**
	 * Returns index value as an integer.
	 * Whitespace is treated as missing: it returns the default or raises the same
	 * exception as an absent value, unlike has().
	 */
	public static function getInt(string $index, ?int $default = null): int {
		return self::getX(
			index: $index,
			default: $default,
			filter: static fn($value) => filter_var(
				$value,
				FILTER_VALIDATE_INT,
				FILTER_NULL_ON_FAILURE,
			),
		);
	}

	/**
	 * Returns index value as a float.
	 * Whitespace is treated as missing: it returns the default or raises the same
	 * exception as an absent value, unlike has().
	 */
	public static function getFloat(string $index, ?float $default = null): float {
		return self::getX(
			index: $index,
			default: $default,
			filter: static fn($value) => filter_var(
				$value,
				FILTER_VALIDATE_FLOAT,
				FILTER_NULL_ON_FAILURE,
			),
		);
	}

	/**
	 * Returns index value as an array of strings with integer keys.
	 *
	 * @param ?array<int, string> $default
	 * @return array<int, string>
	 */
	public static function getArray(string $index, ?array $default = null): array {
		$type = Type::array(valueType: Type::string(), keyType: Type::int());
		$value = ArrayValue::getOrDefault($_REQUEST, $index, $type, $default);
		if ($value === null) {
			throw new Exception('No request variable "' . $index . '"');
		}
		return $value;
	}

	/**
	 * Returns index value as an array of integers with integer keys.
	 *
	 * @param ?array<int, int> $default
	 * @return array<int, int>
	 */
	public static function getIntArray(string $index, ?array $default = null): array {
		$type = Type::array(keyType: Type::int());
		$value = ArrayValue::getOrDefault($_REQUEST, $index, $type, $default);
		if ($value === null) {
			throw new Exception('No request variable "' . $index . '"');
		}
		$result = [];
		foreach ($value as $key => $item) {
			$int = filter_var($item, FILTER_VALIDATE_INT);
			if ($int === false) {
				throw new Exception(
					'Value is not int for index `' . $index . '` at key `' . $key . '`: '
					. var_export($item, true),
				);
			}
			$result[$key] = $int;
		}
		return $result;
	}

	/**
	 * Returns index value as a (trimmed) string.
	 */
	public static function get(string $index, ?string $default = null): string {
		$value = ArrayValue::getOrDefault($_REQUEST, $index, Type::string(), $default);
		if ($value === null) {
			throw new Exception('No request variable "' . $index . '"');
		}
		return trim($value);
	}

	/**
	 * Returns index value as a string from either $_REQUEST or Session.
	 * This is useful for processing pages that need to handle data both from
	 * posted form inputs and from saved Session variables.
	 *
	 * Note that this does not save the result in Session data.
	 */
	public static function getVar(string $index, ?string $default = null): string {
		return self::getVarX($index, $default, self::get(...), Type::string());
	}

	/**
	 * Like getVar, but returns an int instead of a string.
	 */
	public static function getVarInt(string $index, ?int $default = null): int {
		return self::getVarX($index, $default, self::getInt(...), Type::int());
	}

	/**
	 * Like getVar, but returns an array of ints instead of a string.
	 *
	 * @param ?array<int, int> $default
	 * @return array<int, int>
	 */
	public static function getVarIntArray(string $index, ?array $default = null): array {
		$type = Type::array(valueType: Type::int(), keyType: Type::int());
		return self::getVarX($index, $default, self::getIntArray(...), $type);
	}

	/**
	 * Helper function to avoid code duplication in getVar* functions.
	 *
	 * @template T
	 * @param ?T $default
	 * @param callable(string, ?T): T $func
	 * @param Type<T> $type
	 * @return T
	 */
	private static function getVarX(string $index, mixed $default, callable $func, Type $type): mixed {
		$var = Session::getInstance()->getRequestData();
		if (isset($var[$index])) {
			$value = ArrayValue::get($var, $index, $type);
			// An index may be present in both var and request. This indicates
			// a logical error in the code, unless the values are the same,
			// which can occur if, e.g., player refreshes a page (this is OK).
			if (self::has($index) && $value !== $func($index, $default)) {
				throw new Exception('Index "' . $index . '" inconsistent between $var and $_REQUEST!');
			}
			return $value;
		}
		return $func($index, $default);
	}

	public static function getUploadError(string $index): int {
		return ArrayValue::get(self::getUploadedFile($index), 'error', Type::int());
	}

	public static function getUploadedTmpName(string $index): string {
		return ArrayValue::get(self::getUploadedFile($index), 'tmp_name', Type::string());
	}

	/**
	 * @return array<mixed>
	 */
	private static function getUploadedFile(string $index): array {
		return ArrayValue::get($_FILES, $index, Type::array());
	}

}
