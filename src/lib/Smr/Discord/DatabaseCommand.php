<?php declare(strict_types=1);

namespace Smr\Discord;

use Smr\Database;
use SmrPlayer;

/**
 * Use for any discord commands that require a database connection.
 */
abstract class DatabaseCommand extends Command {

	/**
	 * Player associated with the Discord user that invoked the command.
	 */
	protected SmrPlayer $player;

	/**
	 * Constructs a textual response to a DatabaseCommand invocation.
	 *
	 * @return array<string>
	 */
	abstract public function databaseResponse(string ...$args): array;

	/**
	 * Wrapper to properly handle a DatabaseCommand response.
	 */
	final public function response(string ...$args): array {
		// Since we close the database connection after each call, we may
		// need to reconnect here.
		$db = Database::getInstance();
		$db->reconnect();

		try {
			$link = new PlayerLink($this->message);
			if (!$link->valid) {
				return [];
			}
			$this->player = $link->player;
			return $this->databaseResponse(...$args);
		} finally {
			// Close the database connection after a command has executed.
			// This is necessary to prevent a blocking database timeout error.
			$db->close();
		}
	}

}
