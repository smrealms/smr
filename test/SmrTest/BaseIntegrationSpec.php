<?php declare(strict_types=1);

namespace SmrTest;

use mysqli;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Throwable;

class BaseIntegrationSpec extends TestCase {
	protected static mysqli $conn;
	private static array $defaultPopulatedTables = [];

	public static function setUpBeforeClass() : void {
		if (!isset(self::$conn)) {
			self::$conn = DiContainer::make(mysqli::class);
			$query = "SELECT table_name FROM information_schema.tables WHERE table_rows > 0 AND TABLE_SCHEMA='smr_live'";
			$rs = self::$conn->query($query);
			$all = $rs->fetch_all();
			array_walk_recursive($all, function($a) {
				self::$defaultPopulatedTables[] = "'" . $a . "'";
			});
		}
	}

	protected function onNotSuccessfulTest(Throwable $t) : void {
		$this->cleanUp();
		throw $t;
	}

	protected function tearDown() : void {
		$this->cleanUp();
	}

	protected function cleanUp() : void {
		$implode = implode(",", self::$defaultPopulatedTables);
		$query = "SELECT Concat('TRUNCATE TABLE ', TABLE_NAME, ';') FROM INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = 'smr_live' and TABLE_NAME not in (${implode})";
		$rs = self::$conn->query($query);
		$all = $rs->fetch_all();
		foreach ($all as $truncate) {
			self::$conn->query($truncate[0]);
		}
	}
}
