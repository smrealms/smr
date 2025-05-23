<?php declare(strict_types=1);

namespace SmrTest;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;

/**
 * Any test that modifies the database should inherit from this class.
 */
abstract class BaseIntegrationSpec extends TestCase {

	/**
	 * @return array<string>
	 */
	abstract protected function tablesToTruncate(): array;

	private static Connection $conn;
	/** @var array<string, int> */
	private static array $checksums;

	/**
	 * Get checksums for the initial state of the database tables.
	 */
	#[BeforeClass]
	final public static function initializeTableRowCounts(): void {
		if (!isset(self::$conn)) {
			self::$conn = DiContainer::makeClass(Connection::class);
			self::$checksums = self::getChecksums();
		}
	}

	/**
	 * Any table that is modified during a test class should be declared in the
	 * `tablesToTruncate()` method, and those tables will be reset after each
	 * test method.
	 */
	#[After]
	final protected function truncateTables(): void {
		foreach ($this->tablesToTruncate() as $name) {
			// Include hard-coded test database name as a safety precaution
			self::$conn->executeStatement('TRUNCATE TABLE smr_live_test.`' . $name . '`');
		}
	}

	/**
	 * All modified tables should be reset after each test, but here we perform
	 * a final sanity check to make sure that no tables have changed checksums.
	 * This is only done once per class because it is expensive!
	 */
	#[AfterClass]
	final public static function checkTables(): void {
		$checksums = self::getChecksums();
		$errors = [];
		foreach (self::$checksums as $table => $expected) {
			try {
				self::assertSame($expected, $checksums[$table], 'Unexpected checksum for table: ' . $table);
			} catch (AssertionFailedError $err) {
				$errors[] = $err;
				if ($expected === 0) {
					// For convenience, we truncate this table now to avoid
					// issues with rerunning tests.
					self::$conn->executeStatement('TRUNCATE TABLE ' . $table);
				}
			}
		}
		self::assertEquals(self::$checksums, $checksums, implode("\n", $errors));
	}

	/**
	 * @return array<string>
	 */
	private static function getTableNames(): array {
		$query = 'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=\'smr_live_test\'';
		return self::$conn->fetchFirstColumn($query);
	}

	/**
	 * @return array<int>
	 */
	private static function getChecksums(): array {
		$query = 'CHECKSUM TABLE ' . implode(', ', self::getTableNames());
		return self::$conn->fetchAllKeyValue($query);
	}

}
