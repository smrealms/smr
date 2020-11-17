<?php declare(strict_types=1);

use Dotenv\Dotenv;

class MySqlProperties {
	private const CONFIG_MYSQL_HOST = "MYSQL_HOST";
	private const CONFIG_MYSQL_USER = "MYSQL_USER";
	private const CONFIG_MYSQL_PASSWORD = "MYSQL_PASSWORD";
	private const CONFIG_MYSQL_DATABASE = "MYSQL_DATABASE";
	private string $host;
	private string $user;
	private string $password;
	private string $databaseName;

	public function __construct() {
		$config = Dotenv::createArrayBacked(ROOT);
		$array = $config->load();
		self::validateConfig($config);
		[
			self::CONFIG_MYSQL_HOST => $this->host,
			self::CONFIG_MYSQL_USER => $this->user,
			self::CONFIG_MYSQL_PASSWORD => $this->password,
			self::CONFIG_MYSQL_DATABASE => $this->databaseName,
		] = $array;
	}

	public static function validateConfig(Dotenv $config) {
		$config->required(self::CONFIG_MYSQL_HOST);
		$config->required(self::CONFIG_MYSQL_USER);
		$config->required(self::CONFIG_MYSQL_PASSWORD);
		$config->required(self::CONFIG_MYSQL_DATABASE);
	}

	public function getHost(): string {
		return $this->host;
	}

	public function getUser(): string {
		return $this->user;
	}

	public function getPassword(): string {
		return $this->password;
	}

	public function getDatabaseName(): string {
		return $this->databaseName;
	}
}
