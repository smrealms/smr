<?php declare(strict_types=1);

namespace Smr;

class DatabaseRecord {

	/**
	 * @param array $dbRecord A record from a DatabaseResult.
	 */
	public function __construct(
		private array $dbRecord
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
		return match($this->dbRecord[$name]) {
			'TRUE' => true,
			'FALSE' => false,
		};
	}

	public function getInt(string $name): int {
		return (int)$this->dbRecord[$name];
	}

	public function getFloat(string $name): float {
		return (float)$this->dbRecord[$name];
	}

	public function getMicrotime(string $name): string {
		// All digits of precision are stored in a MySQL bigint
		$data = $this->dbRecord[$name];
		return sprintf('%f', $data / 1E6);
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
