<?php declare(strict_types=1);

/**
 * Should be used for getting request data for processing pages.
 * For display pages, see SmrSession::getRequestVar.
 */
class Request {

	/**
	 * Returns true if index is set.
	 * Note that this must be used for checkboxes, since the element is not
	 * posted if a box is unchecked.
	 */
	public static function has(string $index) : bool {
		return isset($_REQUEST[$index]);
	}

	/**
	 * Returns index value as an integer.
	 */
	public static function getInt(string $index, int $default = null) : int {
		if (self::has($index)) {
			return (int)$_REQUEST[$index];
		} elseif (!is_null($default)) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as a float.
	 */
	public static function getFloat(string $index, float $default = null) : float {
		if (self::has($index)) {
			return (float)$_REQUEST[$index];
		} elseif (!is_null($default)) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as an array of strings.
	 */
	public static function getArray(string $index, array $default = null) : array {
		if (self::has($index)) {
			return $_REQUEST[$index];
		} elseif (!is_null($default)) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as an array of integers.
	 */
	public static function getIntArray(string $index, array $default = null) : array {
		if (self::has($index)) {
			$result = [];
			foreach ($_REQUEST[$index] as $key => $value) {
				$result[$key] = (int)$value;
			}
			return $result;
		} elseif (!is_null($default)) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as a string.
	 */
	public static function get(string $index, string $default = null) : string {
		if (self::has($index)) {
			return $_REQUEST[$index];
		} elseif (!is_null($default)) {
			return $default;
		}
		throw new Exception('No request variable "' . $index . '"');
	}

	/**
	 * Returns index value as a string from either $_REQUEST or $var.
	 * This is useful for processing pages that need to handle data both from
	 * posted form inputs and from container variables.
	 *
	 * Note that this does not save the result in $var (see SmrSession).
	 */
	public static function getVar(string $index, string $default = null) : string {
		global $var;
		if (isset($var[$index])) {
			if (self::has($index)) {
				throw new Exception('Index "' . $index . '" must not be in both $var and $_REQUEST!');
			}
			return $var[$index];
		}
		return self::get($index, $default);
	}

	/**
	 * Like getVar, but returns an int instead of a string.
	 */
	public static function getVarInt(string $index, int $default = null) : int {
		global $var;
		if (isset($var[$index])) {
			if (self::has($index)) {
				throw new Exception('Index "' . $index . '" must not be in both $var and $_REQUEST!');
			}
			return $var[$index];
		}
		return self::getInt($index, $default);
	}

}
