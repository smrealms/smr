<?php declare(strict_types=1);

namespace Smr\Discord;

use AbstractSmrPlayer;
use Discord\Parts\Channel\Message;
use Discord\Parts\Thread\Thread;
use Smr\Database;
use Smr\Exceptions\AccountNotFound;
use Smr\Exceptions\AllianceNotFound;
use Smr\Exceptions\PlayerNotFound;
use Smr\Exceptions\UserError;
use SmrAccount;
use SmrAlliance;
use SmrGame;

/**
 * Holds information linking the received message and the game data
 */
class PlayerLink {

	/**
	 * Identifies if the message is linked to game data
	 */
	public bool $valid = false;
	public AbstractSmrPlayer $player;

	public function __construct(Message $message) {
		// force update in case the ID has been changed in-game
		$user_id = $message->author->id;

		try {
			$account = SmrAccount::getAccountByDiscordId($user_id, true);
		} catch (AccountNotFound) {
			throw new UserError("There is no SMR account associated with your Discord User ID. To set this up, go to `Preferences` in-game and set `$user_id` as your `Discord User ID`.");
		}

		// Handle if the message was sent in a thread
		if ($message->channel instanceof Thread) {
			$channel = $message->channel->parent;
		} else {
			$channel = $message->channel;
		}

		if ($channel->is_private) {
			// Get the most recent enabled game, since there is no other way to determine the game ID
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT MAX(game_id) FROM game WHERE enabled=' . $db->escapeBoolean(true) . ' AND end_time > ' . $db->escapeNumber(time()) . ' GROUP BY enabled');
			if (!$dbResult->hasRecord()) {
				throw new UserError('Could not find any games!');
			}
			$game_id = $dbResult->record()->getInt('MAX(game_id)');
		} else {
			// Find the alliance associated with this public channel
			// force update in case the ID has been changed in-game
			$channel_id = $channel->id;
			try {
				$alliance = SmrAlliance::getAllianceByDiscordChannel($channel_id, true);
			} catch (AllianceNotFound) {
				throw new UserError("There is no SMR alliance associated with this Discord Channel ID. To set this up (you must have permission to set this for your alliance), go to `Change Alliance Stats` and set `$channel_id` as your `Discord Channel ID`.\n\n-- If this Discord Channel is public, you probably want to choose a different channel for your alliance.\n-- If you are not in an alliance (or if your alliance doesn't want a channel), send your command again in a direct message to me.");
			}
			$game_id = $alliance->getGameID();
		}

		// Get their player associated with this game
		try {
			$player = AbstractSmrPlayer::getPlayer($account->getAccountID(), $game_id, true);
		} catch (PlayerNotFound) {
			throw new UserError('You have not joined game `' . SmrGame::getGame($game_id)->getName() . '` yet!');
		}

		// Prevent players from leaking sensitive data in other alliance channels
		if (isset($alliance) && $player->getAllianceID() != $alliance->getAllianceID()) {
			throw new UserError('Player `' . $player->getPlayerName() . '` is not a member of alliance `' . $alliance->getAllianceName() . '`');
		}

		// If here, we did not trigger one of the error messages
		$this->player = $player;
		$this->valid = true;
	}

}
