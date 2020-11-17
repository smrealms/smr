<?php

namespace SmrTest;

use mysqli;
use Smr\MySqlProperties;
use PHPUnit\Framework\TestCase;
use Throwable;

class BaseIntegrationSpec extends TestCase {
	protected static mysqli $conn;
	private static $defaultPopulatedTables = array();

	public static function setUpBeforeClass(): void {
		$mysqlProperties = new MySqlProperties();
		print "Attempting to connect to MySQL at " . $mysqlProperties->getHost() . "\n";
		self::$conn = mysqli_connect(
			$mysqlProperties->getHost(),
			$mysqlProperties->getUser(),
			$mysqlProperties->getPassword(),
			$mysqlProperties->getDatabaseName());
		print "Connected.\n";
		$query = "SELECT table_name FROM information_schema.tables WHERE table_rows > 0 AND TABLE_SCHEMA='smr_live'";
		$rs = self::$conn->query($query);
		$all = $rs->fetch_all();
		array_walk_recursive($all, function ($a) {
			self::$defaultPopulatedTables[] = "'" . $a . "'";
		});
	}

	protected function onNotSuccessfulTest(Throwable $t): void {
		$this->cleanUp();
		throw $t;
	}

	protected function tearDown(): void {
		$this->cleanUp();
	}

	private function cleanUp() {
		echo "Cleaning non-default populated tables for next test...\n";
		$implode = implode(",", self::$defaultPopulatedTables);
		$query = "SELECT Concat('TRUNCATE TABLE ', TABLE_NAME, ';') FROM INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = 'smr_live' and TABLE_NAME not in (${implode})";
		$rs = self::$conn->query($query);
		$all = $rs->fetch_all();
		foreach ($all as $truncate) {
			self::$conn->query($truncate[0]);
		}
	}
}
