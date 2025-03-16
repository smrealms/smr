<?php declare(strict_types=1);

namespace Smr\Discord;

use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Exception;
use Smr\AbstractPlayer;
use Smr\Account;
use Smr\Alliance;
use Smr\Database;
use Smr\Exceptions\AccountNotFound;
use Smr\Exceptions\AllianceNotFound;
use Smr\Exceptions\PlayerNotFound;
use Smr\Exceptions\UserError;
use Smr\Game;

/**
 * Holds information linking the received message and the game data
 */
class PlayerLink {

	public readonly AbstractPlayer $player;

	public function __construct(Message $message) {
		// force update in case the ID has been changed in-game
		$user_id = $message->author?->id;
		if ($user_id === null) {
			throw new Exception('This message does not have an author somehow!');
		}

		try {
			$account = Account::getAccountByDiscordId($user_id, true);
		} catch (AccountNotFound) {
			throw new UserError("There is no SMR account associated with your Discord User ID. To set this up, go to `Preferences` in-game and set `$user_id` as your `Discord User ID`.");
		}

		$channel = $message->channel; // might be Channel or Thread
		if ($channel instanceof Channel && $channel->is_private) {
			// Get the most recent enabled game, since there is no other way to determine the game ID
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT MAX(game_id) FROM game WHERE enabled = :enabled AND end_time > :now', [
				'enabled' => $db->escapeBoolean(true),
				'now' => $db->escapeNumber(time()),
			]);
			$game_id = $dbResult->record()->getNullableInt('MAX(game_id)');
			if ($game_id === null) {
				throw new UserError('Could not find any games!');
			}
		} else {
			// Find the alliance associated with this public channel
			// force update in case the ID has been changed in-game
			$channel_id = $channel->id;
			try {
				$alliance = Alliance::getAllianceByDiscordChannel($channel_id, true);
			} catch (AllianceNotFound) {
				throw new UserError("There is no SMR alliance associated with this Discord Channel ID. To set this up (you must have permission to set this for your alliance), go to `Change Alliance Stats` and set `$channel_id` as your `Discord Channel ID`.\n\n-- If this Discord Channel is public, you probably want to choose a different channel for your alliance.\n-- If you are not in an alliance (or if your alliance doesn't want a channel), send your command again in a direct message to me.");
			}
			$game_id = $alliance->getGameID();
		}

		// Get their player associated with this game
		try {
			$player = AbstractPlayer::getPlayer($account->getAccountID(), $game_id, true);
		} catch (PlayerNotFound) {
			throw new UserError('You have not joined game `' . Game::getGame($game_id)->getName() . '` yet!');
		}

		// Prevent players from leaking sensitive data in other alliance channels
		if (isset($alliance) && $player->getAllianceID() !== $alliance->getAllianceID()) {
			throw new UserError('Player `' . $player->getPlayerName() . '` is not a member of alliance `' . $alliance->getAllianceName() . '`');
		}

		// If here, we did not trigger one of the error messages
		$this->player = $player;
	}

}
