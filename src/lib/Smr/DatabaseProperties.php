<?php declare(strict_types=1);

namespace Smr;

use Dotenv\Dotenv;

class DatabaseProperties {
	private const CONFIG_HOST = 'MYSQL_HOST';
	private const CONFIG_USER = 'MYSQL_USER';
	private const CONFIG_PASSWORD = 'MYSQL_PASSWORD';
	private const CONFIG_DATABASE = 'MYSQL_DATABASE';
	private string $host;
	private string $user;
	private string $password;
	private string $databaseName;

	public function __construct(Dotenv $config) {
		$array = $config->load();
		self::validateConfig($config);
		[
			self::CONFIG_HOST => $this->host,
			self::CONFIG_USER => $this->user,
			self::CONFIG_PASSWORD => $this->password,
			self::CONFIG_DATABASE => $this->databaseName,
		] = $array;
	}

	private static function validateConfig(Dotenv $config): void {
		$config->required([
			self::CONFIG_HOST,
			self::CONFIG_USER,
			self::CONFIG_PASSWORD,
			self::CONFIG_DATABASE,
		])->notEmpty();
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
