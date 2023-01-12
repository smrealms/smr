<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\TestCase;
use Smr\Database;

/**
 * This is an extension of DatabaseIntegrationTest. It is separate due to the
 * need for specific setUp and tearDown functions (which we do not want to use
 * for every other DatabaseIntegrationTest method).
 *
 * @covers \Smr\Database
 */
class DatabaseInsertTest extends TestCase {

	protected function setUp(): void {
		// Create temporary tables
		$db = Database::getInstance();
		$db->write('CREATE TABLE `test1` (`var` text DEFAULT NULL)');
		$db->write('CREATE TABLE `test2` (`id` int NOT NULL AUTO_INCREMENT, `var` text DEFAULT NULL, PRIMARY KEY (`id`))');
	}

	protected function tearDown(): void {
		// Remove temporary tables
		$db = Database::getInstance();
		$db->write('DROP TABLE test1');
		$db->write('DROP TABLE test2');
	}

	public function test_insert(): void {
		$db = Database::getInstance();

		// Zero insert ID when table does not have an auto-increment column
		self::assertSame(0, $db->insert('test1', []));

		// Non-zero insert ID when table has an auto-increment column
		for ($i = 1; $i <= 3; $i++) {
			self::assertSame($i, $db->insert('test2', []));
		}

		// Non-empty fields are successfully recovered
		$logID = $db->insert('test2', [
			'var' => $db->escapeString('foo'),
		]);
		$result = $db->read('SELECT * FROM test2 WHERE id = ' . $logID);
		$record = $result->record();
		self::assertSame('foo', $record->getString('var'));
	}

	public function test_replace(): void {
		$db = Database::getInstance();

		// Zero insert ID when table does not have an auto-increment column
		self::assertSame(0, $db->replace('test1', []));

		// Non-zero insert ID when table has an auto-increment column
		for ($i = 1; $i <= 3; $i++) {
			self::assertSame($i, $db->replace('test2', []));
		}

		// Replacing an existing row returns that row as the insert ID
		$logID = $db->replace('test2', [
			'id' => 2,
			'var' => $db->escapeString('foo'),
		]);
		self::assertSame(2, $logID);

		// Non-empty fields are successfully recovered
		$result = $db->read('SELECT * FROM test2 WHERE id = ' . $logID);
		$record = $result->record();
		self::assertSame('foo', $record->getString('var'));
	}

}
