<?php declare(strict_types=1);

namespace Smr;

use Doctrine\DBAL\Result;
use Exception;
use Generator;
use RuntimeException;

/**
 * Holds the result of a Database query (e.g. read or write).
 */
class DatabaseResult {

	public function __construct(
		private readonly Result $dbResult,
	) {}

	/**
	 * Use to iterate over the records from the result set.
	 * @return \Generator<DatabaseRecord>
	 */
	public function records(): Generator {
		foreach ($this->dbResult->iterateAssociative() as $dbRecord) {
			yield new DatabaseRecord($dbRecord);
		}
	}

	/**
	 * Use when exactly one record is expected from the result set.
	 */
	public function record(): DatabaseRecord {
		if ($this->getNumRecords() !== 1) {
			throw new RuntimeException('One record required, but found ' . $this->getNumRecords());
		}
		$record = $this->dbResult->fetchAssociative();
		if ($record === false) {
			throw new Exception('Do not call record twice on the same result');
		}
		return new DatabaseRecord($record);
	}

	public function getNumRecords(): int {
		return (int)$this->dbResult->rowCount();
	}

	public function hasRecord(): bool {
		return $this->getNumRecords() > 0;
	}

}
