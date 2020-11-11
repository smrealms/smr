<?php declare(strict_types=1);


use Dotenv\Dotenv;


class MysqlProperties {
	private const CONFIG_MYSQL_HOST = "MYSQL_HOST";
	private const CONFIG_MYSQL_USER = "MYSQL_USER";
	private const CONFIG_MYSQL_PASSWORD = "MYSQL_PASSWORD";
	private const CONFIG_MYSQL_PORT = "MYSQL_PORT";
	private const CONFIG_MYSQL_DATABASE = "MYSQL_DATABASE";
	private string $host;
	private string $user;
	private string $password;
	private int $port;
	private string $databaseName;

	public function __construct() {
		$configArray = !isset($_ENV["MYSQL_CONFIG_FROM_ENVIRONMENT"])
			? Dotenv::createArrayBacked(ROOT)->load()
			: $_ENV;
		$this->parseConfigArray($configArray);
	}

	private function parseConfigArray(array $array) {
		[
			MysqlProperties::CONFIG_MYSQL_HOST => $this->host,
			MysqlProperties::CONFIG_MYSQL_USER => $this->user,
			MysqlProperties::CONFIG_MYSQL_PASSWORD => $this->password,
			MysqlProperties::CONFIG_MYSQL_PORT => $port,
			MysqlProperties::CONFIG_MYSQL_DATABASE => $this->databaseName,
		] = $array;
		$this->port = (int)$port;
	}

	/**
	 * @return string
	 */
	public function getHost(): string {
		return $this->host;
	}

	/**
	 * @return string
	 */
	public function getUser(): string {
		return $this->user;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string {
		return $this->password;
	}

	/**
	 * @return int
	 */
	public function getPort(): int {
		return $this->port;
	}

	/**
	 * @return string
	 */
	public function getDatabaseName(): string {
		return $this->databaseName;
	}
}
