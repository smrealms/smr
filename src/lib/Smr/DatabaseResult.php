<?php declare(strict_types=1);

namespace Smr;

/**
 * Holds the result of a Database query (e.g. read or write).
 */
class DatabaseResult {

	public function __construct(
		private \mysqli_result $dbResult
	) {}

	/**
	 * Use to iterate over the records from the result set.
	 * @return \Generator<DatabaseRecord>
	 */
	public function records(): \Generator {
		foreach ($this->dbResult as $dbRecord) {
			yield new DatabaseRecord($dbRecord);
		}
	}

	/**
	 * Use when exactly one record is expected from the result set.
	 */
	public function record(): DatabaseRecord {
		if ($this->getNumRecords() != 1) {
			throw new \RuntimeException('One record required, but found ' . $this->getNumRecords());
		}
		return new DatabaseRecord($this->dbResult->fetch_assoc());
	}

	public function getNumRecords(): int {
		return $this->dbResult->num_rows;
	}

	public function hasRecord(): bool {
		return $this->getNumRecords() > 0;
	}

}
