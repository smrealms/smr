<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\TypeHelpers\BoolType;
use Smr\TypeHelpers\FloatType;
use Smr\TypeHelpers\IntType;
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
	 * @param Type<T> $type
	 * @return T
	 */
	private static function getX(string $index, mixed $default, Type $type): mixed {
		if (self::has($index)) {
			$filter = match ($type::class) {
				BoolType::class => FILTER_VALIDATE_BOOL,
				IntType::class => FILTER_VALIDATE_INT,
				FloatType::class => FILTER_VALIDATE_FLOAT,
				default => throw new Exception('Unsupported request value type: ' . $type->name()),
			};
			// Need null on failure to handle bool failures
			$value = filter_var($_REQUEST[$index], $filter, FILTER_NULL_ON_FAILURE);
			if ($value === null) {
				throw new Exception(
					'Value is not ' . $type->name() . ' for index `' . $index . '`: '
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
		return self::getX($index, $default, Type::bool());
	}

	/**
	 * Returns index value as an integer.
	 */
	public static function getInt(string $index, ?int $default = null): int {
		return self::getX($index, $default, Type::int());
	}

	/**
	 * Returns index value as a float.
	 */
	public static function getFloat(string $index, ?float $default = null): float {
		return self::getX($index, $default, Type::float());
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
	 * Returns index value as a string from either $_REQUEST or $var.
	 * This is useful for processing pages that need to handle data both from
	 * posted form inputs and from container variables.
	 *
	 * Note that this does not save the result in $var (see Smr\Session).
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
