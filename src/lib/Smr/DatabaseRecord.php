<?php declare(strict_types=1);

namespace Smr;

use BackedEnum;
use Exception;

class DatabaseRecord {

	/**
	 * @param array<string, mixed> $dbRecord A record from a DatabaseResult.
	 */
	public function __construct(
		private readonly array $dbRecord,
	) {}

	public function getNullableString(string $name): ?string {
		if ($this->dbRecord[$name] === null) {
			return null;
		}
		return $this->getString($name);
	}

	public function getString(string $name): string {
		$value = $this->dbRecord[$name];
		if (!is_string($value)) {
			throw new Exception('Expected record `' . $name . '` to be a string, got ' . get_debug_type($value));
		}
		return $value;
	}

	public function getNullableBoolean(string $name): ?bool {
		if ($this->dbRecord[$name] === null) {
			return null;
		}
		return $this->getBoolean($name);
	}

	public function getBoolean(string $name): bool {
		return match ($this->dbRecord[$name]) {
			'TRUE' => true,
			'FALSE' => false,
			default => throw new Exception('Unexpected boolean record: ' . var_export($this->dbRecord[$name], true)),
		};
	}

	public function getNullableInt(string $name): ?int {
		if ($this->dbRecord[$name] === null) {
			return null;
		}
		return $this->getInt($name);
	}

	public function getInt(string $name): int {
		$result = filter_var($this->dbRecord[$name], FILTER_VALIDATE_INT);
		if ($result === false) {
			throw new Exception('Failed to convert ' . var_export($this->dbRecord[$name], true) . ' to int');
		}
		return $result;
	}

	public function getFloat(string $name): float {
		$result = filter_var($this->dbRecord[$name], FILTER_VALIDATE_FLOAT);
		if ($result === false) {
			throw new Exception('Failed to convert ' . var_export($this->dbRecord[$name], true) . ' to float');
		}
		return $result;
	}

	public function getNullableObject(string $name, bool $compressed = false): mixed {
		if ($this->dbRecord[$name] === null) {
			return null;
		}
		return $this->getObject($name, $compressed);
	}

	public function getObject(string $name, bool $compressed = false): mixed {
		$object = $this->getString($name);
		if ($compressed === true) {
			$object = gzuncompress($object);
			if ($object === false) {
				throw new Exception('Failed to uncompress database record: ' . $name);
			}
		}
		return unserialize($object);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $class
	 * @return T
	 */
	public function getClass(string $name, string $class, bool $compressed = false): mixed {
		$object = $this->getObject($name, $compressed);
		if (!($object instanceof $class)) {
			throw new Exception('Value ' . var_export($object, true) . ' is not of type ' . $class);
		}
		return $object;
	}

	/**
	 * @template T of BackedEnum
	 * @param class-string<T> $enum
	 * @return T
	 */
	public function getIntEnum(string $name, string $enum): BackedEnum {
		return $enum::from($this->getInt($name));
	}

	/**
	 * @template T of BackedEnum
	 * @param class-string<T> $enum
	 * @return T
	 */
	public function getStringEnum(string $name, string $enum): BackedEnum {
		return $enum::from($this->getString($name));
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getRow(): array {
		return $this->dbRecord;
	}

}
