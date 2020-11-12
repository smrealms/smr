<?php
/**
 * > set global general_log_file = "/var/log/mysql/queries.log";
 * > set global general_log = "ON";
 * [wait some time, hit some pages, whatever]
 * > set global general_log = "OFF";
 */

namespace SmrTest;

use mysqli;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Smr\MysqlProperties;
use Throwable;


class BaseIntegrationSpec extends TestCase {
	protected static mysqli $conn;
	private static $defaultPopulatedTables = array();
	private const MYSQL_CONNECTION_ATTEMPTS = 5;
	private const MYSQL_CONNECTION_RETRY_SECONDS = 5;

	public static function setUpBeforeClass(): void {
		$mysqlProperties = new MysqlProperties();
		if (self::$conn = self::getMysqlConnection($mysqlProperties)) {
			// Reset environment variables for flyway. Unfortunately adding -e to the docker-compose command does not take precedence.
			putenv("MYSQL_PORT=3306");
			exec("docker-compose run --rm flyway-integration-test 1>&2");
			$query = "SELECT table_name FROM information_schema.tables WHERE table_rows > 0 AND TABLE_SCHEMA='smr_live'";
			$rs = self::$conn->query($query);
			$all = $rs->fetch_all();
			array_walk_recursive($all, function ($a) {
				self::$defaultPopulatedTables[] = "'" . $a . "'";
			});
		}
	}

	private static function getMysqlConnection(MysqlProperties $mysqlProperties, $attempt = 0): mysqli {
		putenv("MYSQL_HOST=smr-mysql");
		while ($attempt < self::MYSQL_CONNECTION_ATTEMPTS) {
			print "#${attempt}: Attempting to connect to MySQL on " . $mysqlProperties->getHost() . ":" . $mysqlProperties->getPort() . "...\n";
			$conn = @mysqli_connect(
				$mysqlProperties->getHost(),
				$mysqlProperties->getUser(),
				$mysqlProperties->getPassword(),
				$mysqlProperties->getDatabaseName(),
				$mysqlProperties->getPort());
			if ($conn) {
				print "Connection successful.\n";
				return $conn;
			}
			if ($attempt == 0) {
				print "Starting up mysql-integration-test container...\n";
				exec("docker-compose up -d mysql-integration-test 1>&2");
			}
			$attempt += 1;
			print "Attempt failed -- retrying in " . self::MYSQL_CONNECTION_RETRY_SECONDS . " seconds...\n";
			sleep(self::MYSQL_CONNECTION_RETRY_SECONDS);
			return self::getMysqlConnection($mysqlProperties, $attempt);
		}
		throw new RuntimeException("Could not reach MySQL after $attempt tries.");
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
