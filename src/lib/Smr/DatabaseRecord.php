<?php declare(strict_types=1);

namespace Smr;

use Exception;

class DatabaseRecord {

	/**
	 * @param array $dbRecord A record from a DatabaseResult.
	 */
	public function __construct(
		private readonly array $dbRecord
	) {}

	public function hasField(string $name): bool {
		return isset($this->dbRecord[$name]);
	}

	public function getField(string $name): ?string {
		return $this->dbRecord[$name];
	}

	/**
	 * Get a string-only field from the database record.
	 * If the field can be null, use `getField` instead.
	 */
	public function getString(string $name): string {
		return $this->dbRecord[$name];
	}

	public function getBoolean(string $name): bool {
		return match ($this->dbRecord[$name]) {
			'TRUE' => true,
			'FALSE' => false,
		};
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

	public function getObject(string $name, bool $compressed = false, bool $nullable = false): mixed {
		$object = $this->getField($name);
		if ($nullable === true && $object === null) {
			return null;
		}
		if ($compressed === true) {
			$object = gzuncompress($object);
		}
		return unserialize($object);
	}

	public function getRow(): array {
		return $this->dbRecord;
	}

}
