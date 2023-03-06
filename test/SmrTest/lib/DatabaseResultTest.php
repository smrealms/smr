<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Smr\Database;
use Smr\DatabaseResult;

#[CoversClass(DatabaseResult::class)]
class DatabaseResultTest extends TestCase {

	/**
	 * Create and run a trivial query that returns $num rows
	 */
	private function runQuery(int $num): DatabaseResult {
		$db = Database::getInstance();
		// This query will look like (for increasing $num):
		//    SELECT 1 LIMIT 0
		//    SELECT 1 LIMIT 1
		//    SELECT 1 UNION SELECT 2 LIMIT 2
		//    SELECT 1 UNION SELECT 2 UNION SELECT 3 LIMIT 3
		// We always include at least "SELECT 1" so that we have a valid
		// query in the $num=0 case, and then add "LIMIT $num" to ensure
		// that we get the requested number of rows.
		$query = 'SELECT 1';
		for ($i = 2; $i <= $num; $i++) {
			$query .= ' UNION SELECT ' . $i;
		}
		$query .= ' LIMIT ' . $num;
		return $db->read($query);
	}

	public function test_record_one_row(): void {
		self::assertSame([1 => '1'], $this->runQuery(1)->record()->getRow());
	}

	public function test_record_too_many_rows(): void {
		$result = $this->runQuery(2);
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('One record required, but found 2');
		$result->record();
	}

	public function test_record_too_few_rows(): void {
		$result = $this->runQuery(0);
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('One record required, but found 0');
		$result->record();
	}

	public function test_record_called_twice(): void {
		$result = $this->runQuery(1);
		$result->record();
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Do not call record twice on the same result');
		$result->record();
	}

	public function test_hasRecord_no_rows(): void {
		$result = $this->runQuery(0);
		self::assertFalse($result->hasRecord());
	}

	public function test_hasRecord_with_rows(): void {
		$result = $this->runQuery(1);
		self::assertTrue($result->hasRecord());
	}

	public function test_getNumRecords(): void {
		foreach ([0, 1, 2] as $numRecords) {
			$result = $this->runQuery($numRecords);
			self::assertSame($numRecords, $result->getNumRecords());
		}
	}

	public function test_records(): void {
		$numRecords = 0;
		$result = $this->runQuery(3);
		foreach ($result->records() as $index => $record) {
			$row = $index + 1;
			self::assertSame([1 => (string)$row], $record->getRow());
			$numRecords++;
		}
		self::assertSame(3, $numRecords);
	}

	public function test_records_no_rows(): void {
		$result = $this->runQuery(0);
		self::assertSame([], iterator_to_array($result->records()));
	}

}
