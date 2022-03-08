<?php declare(strict_types=1);

namespace Smr;

use Exception;

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
	 * Returns index value as an integer.
	 */
	public static function getInt(string $index, int $default = null): int {
		if (self::has($index)) {
			return (int)$_REQUEST[$index];
		} elseif ($default !== null) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as a float.
	 */
	public static function getFloat(string $index, float $default = null): float {
		if (self::has($index)) {
			return (float)$_REQUEST[$index];
		} elseif ($default !== null) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as an array of strings.
	 */
	public static function getArray(string $index, array $default = null): array {
		if (self::has($index)) {
			return $_REQUEST[$index];
		} elseif ($default !== null) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as an array of integers.
	 */
	public static function getIntArray(string $index, array $default = null): array {
		if (self::has($index)) {
			$result = [];
			foreach ($_REQUEST[$index] as $key => $value) {
				$result[$key] = (int)$value;
			}
			return $result;
		} elseif ($default !== null) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as a string.
	 */
	public static function get(string $index, string $default = null): string {
		if (self::has($index)) {
			return $_REQUEST[$index];
		} elseif ($default !== null) {
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
	public static function getVar(string $index, string $default = null): string {
		return self::getVarX($index, $default, [self::class, 'get']);
	}

	/**
	 * Like getVar, but returns an int instead of a string.
	 */
	public static function getVarInt(string $index, int $default = null): int {
		return self::getVarX($index, $default, [self::class, 'getInt']);
	}

	/**
	 * Like getVar, but returns an array of ints instead of a string.
	 */
	public static function getVarIntArray(string $index, array $default = null): array {
		return self::getVarX($index, $default, [self::class, 'getIntArray']);
	}

	/**
	 * Helper function to avoid code duplication in getVar* functions.
	 */
	private static function getVarX(string $index, mixed $default, callable $func): mixed {
		$var = Session::getInstance()->getCurrentVar();
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

}
