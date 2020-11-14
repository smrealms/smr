<?php declare(strict_types=1);

use Dotenv\Dotenv;

class MySqlProperties {
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
		$environmentFile = self::determineConfigEnvironmentFile();
		$config = Dotenv::createArrayBacked(ROOT, $environmentFile);
		$array = $config->load();
		self::validateConfig($config);
		[
			MySqlProperties::CONFIG_MYSQL_HOST => $this->host,
			MySqlProperties::CONFIG_MYSQL_USER => $this->user,
			MySqlProperties::CONFIG_MYSQL_PASSWORD => $this->password,
			MySqlProperties::CONFIG_MYSQL_PORT => $port,
			MySqlProperties::CONFIG_MYSQL_DATABASE => $this->databaseName,
		] = $array;
		$this->port = (int)$port;
	}

	public static function determineConfigEnvironmentFile(): string {
		$environment = "";
		if (isset($_ENV["MYSQL_CONFIG_ENVIRONMENT"])) {
			$environment = $_ENV["MYSQL_CONFIG_ENVIRONMENT"];
		}
		return $environment . ".env";
	}

	public static function validateConfig(Dotenv $config) {
		$config->required(self::CONFIG_MYSQL_HOST);
		$config->required(self::CONFIG_MYSQL_USER);
		$config->required(self::CONFIG_MYSQL_PASSWORD);
		$config->required(self::CONFIG_MYSQL_PORT)->isInteger();
		$config->required(self::CONFIG_MYSQL_DATABASE);
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
