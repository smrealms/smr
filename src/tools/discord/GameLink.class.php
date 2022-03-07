<?php declare(strict_types=1);

/**
 * Holds information linking the received message and the game data
 */
class GameLink {
	/**
	 * Identifies if the message is linked to game data
	 */
	public bool $valid = false;
	public SmrPlayer $player;

	public function __construct(Discord\Parts\Channel\Message $message) {

		// force update in case the ID has been changed in-game
		$user_id = $message->author->id;

		try {
			$account = SmrAccount::getAccountByDiscordId($user_id, true);
		} catch (Smr\Exceptions\AccountNotFound) {
			$message->reply("There is no SMR account associated with your Discord User ID. To set this up, go to `Preferences` in-game and set `$user_id` as your `Discord User ID`.")
				->done(null, 'logException');
			return;
		}

		if ($message->channel->is_private) {
			// Get the most recent enabled game, since there is no other way to determine the game ID
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT MAX(game_id) FROM game WHERE enabled=' . $db->escapeBoolean(true) . ' AND end_time > ' . $db->escapeNumber(time()));
			if (!$dbResult->hasRecord()) {
				$message->reply('Could not find any games!')
					->done(null, 'logException');
				return;
			}
			$game_id = $dbResult->record()->getInt('MAX(game_id)');

			$this->player = SmrPlayer::getPlayer($account->getAccountID(), $game_id, true);
		} else {
			// Only perform sensitive queries in approved public channels

			// Get the approved channel
			// force update in case the ID has been changed in-game
			$channel_id = $message->channel->id;
			try {
				$alliance = SmrAlliance::getAllianceByDiscordChannel($channel_id, true);
			} catch (Smr\Exceptions\AllianceNotFound) {
				$message->reply("There is no SMR alliance associated with this Discord Channel ID. To set this up (you must have permission to set this for your alliance), go to `Change Alliance Stats` and set `$channel_id` as your `Discord Channel ID`.\n\n-- If this Discord Channel is public, you probably want to choose a different channel for your alliance.\n-- If you are not in an alliance (or if your alliance doesn't want a channel), send your command again in a direct message to me.")
					->done(null, 'logException');
				return;
			}

			$this->player = SmrPlayer::getPlayer($account->getAccountID(), $alliance->getGameID(), true);
			if ($this->player->getAllianceID() != $alliance->getAllianceID()) {
				$message->reply('Player `' . $this->player->getPlayerName() . '` is not a member of alliance `' . $alliance->getAllianceName() . '`')
					->done(null, 'logException');
				return;
			}
		}

		// If here, we did not trigger one of the error messages
		$this->valid = true;
	}
}
