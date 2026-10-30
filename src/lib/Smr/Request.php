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
	 * @param (T is bool ? FILTER_VALIDATE_BOOL : (T is int ? FILTER_VALIDATE_INT : FILTER_VALIDATE_FLOAT)) $filter
	 * @return T
	 */
	private static function getX(string $index, mixed $default, int $filter): mixed {
		if (self::has($index)) {
			// Need null on failure to handle bool failures
			$value = filter_var($_REQUEST[$index], $filter, FILTER_NULL_ON_FAILURE);
			if ($value === null) {
				$type = match ($filter) {
					FILTER_VALIDATE_BOOL => 'bool',
					FILTER_VALIDATE_INT => 'int',
					FILTER_VALIDATE_FLOAT => 'float',
				};
				throw new Exception(
					'Value is not ' . $type . ' for index `' . $index . '`: '
					. var_export($_REQUEST[$index], true),
				);
			}
			return $value;
		}
		if ($default !== null) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as a boolean for boolean-like inputs.
	 */
	public static function getBool(string $index, ?bool $default = null): bool {
		return self::getX($index, $default, FILTER_VALIDATE_BOOL);
	}

	/**
	 * Returns index value as an integer.
	 */
	public static function getInt(string $index, ?int $default = null): int {
		return self::getX($index, $default, FILTER_VALIDATE_INT);
	}

	/**
	 * Returns index value as a float.
	 */
	public static function getFloat(string $index, ?float $default = null): float {
		return self::getX($index, $default, FILTER_VALIDATE_FLOAT);
	}

	/**
	 * Returns index value as an array of strings with integer keys.
	 *
	 * @param ?array<int, string> $default
	 * @return array<int, string>
	 */
	public static function getArray(string $index, ?array $default = null): array {
		if (self::has($index)) {
			return self::getStringArrayValue($index);
		}
		if ($default !== null) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as an array of integers with integer keys.
	 *
	 * @param ?array<int, int> $default
	 * @return array<int, int>
	 */
	public static function getIntArray(string $index, ?array $default = null): array {
		if (self::has($index)) {
			$result = [];
			foreach (self::getStringArrayValue($index) as $key => $value) {
				$result[$key] = (int)$value;
			}
			return $result;
		}
		if ($default !== null) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as a (trimmed) string.
	 */
	public static function get(string $index, ?string $default = null): string {
		if (self::has($index)) {
			$value = $_REQUEST[$index];
			if (!is_string($value)) {
				throw new Exception('Value is not string for index `' . $index . '`: ' . var_export($_REQUEST[$index], true));
			}
			return trim($value);
		}
		if ($default !== null) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as a string from either $_REQUEST or $var.
	 * This is useful for processing pages that need to handle data both from
	 * posted form inputs and from container variables.
	 *
	 * Note that this does not save the result in $var (see Smr\Session).
	 */
	public static function getVar(string $index, ?string $default = null): string {
		$value = self::getVarX($index, $default, self::get(...));
		if (!is_string($value)) {
			throw new Exception('Session variable must be a string: ' . $index);
		}
		return $value;
	}

	/**
	 * Like getVar, but returns an int instead of a string.
	 */
	public static function getVarInt(string $index, ?int $default = null): int {
		$value = self::getVarX($index, $default, self::getInt(...));
		if (!is_int($value)) {
			throw new Exception('Session variable must be an int: ' . $index);
		}
		return $value;
	}

	/**
	 * Like getVar, but returns an array of ints instead of a string.
	 *
	 * @param ?array<int, int> $default
	 * @return array<int, int>
	 */
	public static function getVarIntArray(string $index, ?array $default = null): array {
		$value = self::getVarX($index, $default, self::getIntArray(...));
		if (!is_array($value)) {
			throw new Exception('Session variable must be an int array: ' . $index);
		}
		$result = [];
		foreach ($value as $key => $item) {
			if (!is_int($key) || !is_int($item)) {
				throw new Exception('Session variable must be an int array: ' . $index);
			}
			$result[$key] = $item;
		}
		return $result;
	}

	/**
	 * Helper function to avoid code duplication in getVar* functions.
	 */
	private static function getVarX(string $index, mixed $default, callable $func): mixed {
		$var = Session::getInstance()->getRequestData();
		if (isset($var[$index])) {
			// An index may be present in both var and request. This indicates
			// a logical error in the code, unless the values are the same,
			// which can occur if, e.g., player refreshes a page (this is OK).
			if (self::has($index) && $var[$index] !== $func($index, $default)) {
				throw new Exception('Index "' . $index . '" inconsistent between $var and $_REQUEST!');
			}
			return $var[$index];
		}
		return $func($index, $default);
	}

	/**
	 * @return array<int, string>
	 */
	private static function getStringArrayValue(string $index): array {
		$innerType = Type::array(valueType: Type::string(), keyType: Type::int());
		return ArrayValue::get($_REQUEST, $index, $innerType);
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
