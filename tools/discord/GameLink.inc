<?php declare(strict_types=1);

// Holds information linking the received message and the game data
class GameLink
{
	public $valid = false;  // identifies if the message is linked to game data
	public $account;        // SmrAccount instance
	public $alliance;       // SmrAlliance instance
	public $player;         // SmrPlayer instance

	function __construct(Discord\Parts\Channel\Channel $channel, Discord\Parts\User\User $author) {

		// force update in case the ID has been changed in-game
		$user_id = $author->id;
		$this->account = SmrAccount::getAccountByDiscordId($user_id, true);

		if (is_null($this->account)) {
			$channel->sendMessage("There is no SMR account associated with your Discord User ID. To set this up, go to `Preferences` in-game and set `$user_id` as your `Discord User ID`.");
			return;
		}

		if ($channel->is_private) {
			// Get the most recent enabled game, since there is no other way to determine the game ID
			$db = new SmrMySqlDatabase();
			$db->query('SELECT MAX(game_id) FROM game WHERE enabled=' . $db->escapeBoolean(true) . ' AND end_time > ' . $db->escapeNumber(time()));
			if ($db->nextRecord()) {
				$game_id = $db->getInt('MAX(game_id)');
			} else {
				$channel->sendMessage('Could not find any games!');
				return;
			}

			$this->player = SmrPlayer::getPlayer($this->account->getAccountID(), $game_id, true);
		} else {
			// Only perform sensitive queries in approved public channels

			// Get the approved channel
			// force update in case the ID has been changed in-game
			$channel_id = $channel->id;
			$this->alliance = SmrAlliance::getAllianceByDiscordChannel($channel_id, true);

			if (is_null($this->alliance)) {
				$channel->sendMessage("There is no SMR alliance associated with this Discord Channel ID. To set this up (you must have permission to set this for your alliance), go to `Change Alliance Stats` and set `$channel_id` as your `Discord Channel ID`.\n\n-- If this Discord Channel is public, you probably want to choose a different channel for your alliance.\n-- If you are not in an alliance (or if your alliance doesn't want a channel), send your command again in a direct message to me.");
				return;
			}

			$this->player = SmrPlayer::getPlayer($this->account->getAccountID(), $this->alliance->getGameID(), true);
			if ($this->player->getAllianceID() != $this->alliance->getAllianceID()) {
				$channel->sendMessage("Player `" . $this->player->getPlayerName() . "` is not a member of alliance `" . $this->alliance->getAllianceName() . "`");
				return;
			}
		}

		// If here, we did not trigger one of the error messages
		$this->valid = true;
	}
}
