<?php declare(strict_types=1);

namespace Smr;

use Exception;

/**
 * Expects the following environment variables to be set by the OS:
 *
 *  MYSQL_HOSTNAME
 *  MYSQL_USER
 *  MYSQL_DATABASE
 *  MYSQL_PASSWORD_FILE
 */
class DatabaseProperties {

	public readonly string $host;
	public readonly string $user;
	public readonly string $password;
	public readonly string $database;

	public function __construct() {
		$this->host = MYSQL_HOSTNAME;
		$this->user = $this->getFromEnv('MYSQL_USER');
		$this->database = $this->getFromEnv('MYSQL_DATABASE');
		$passwordFile = $this->getFromEnv('MYSQL_PASSWORD_FILE');
		$this->password = $this->getFromFile($passwordFile);
	}

	private function getFromEnv(string $name): string {
		$value = getenv($name, local_only: true);
		if ($value === false) {
			throw new Exception('Database environment variable is missing: ' . $name);
		}
		return $value;
	}

	private function getFromFile(string $file): string {
		$value = file_get_contents($file);
		if ($value === false) {
			throw new Exception('Failed to read content from file: ' . $file);
		}
		return $value;
	}

}
