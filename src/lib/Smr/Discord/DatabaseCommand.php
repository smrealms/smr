<?php declare(strict_types=1);

namespace Smr\Discord;

use AbstractSmrPlayer;
use Smr\Database;

/**
 * Use for any discord commands that require a database connection.
 */
abstract class DatabaseCommand extends Command {

	/**
	 * Player associated with the Discord user that invoked the command.
	 */
	protected AbstractSmrPlayer $player;

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
			Database::resetInstance();
		}
	}

}
