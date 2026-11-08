<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class MessageBlacklistAddProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly ?int $blacklistPlayerID = null,
	) {}

	public function build(Player $player): never {
		if ($this->blacklistPlayerID !== null) {
			$blacklisted = Player::getPlayer($this->blacklistPlayerID);
		} else {
			try {
				$blacklisted = Player::getPlayerByPlayerName(Request::get('PlayerName'), $player->getGameID());
			} catch (PlayerNotFound) {
				new MessageBlacklist('<span class="red bold">ERROR: </span>Player does not exist.')->go();
			}
		}

		$db = Database::getInstance();
		$params = [
			...$player->SQLID,
			'game_id' => $player->getGameID(),
			'blacklisted_player_id' => $blacklisted->getPlayerID(),
		];
		$dbResult = $db->select('message_blacklist', $params);

		if ($dbResult->hasRecord()) {
			new MessageBlacklist('<span class="red bold">ERROR: </span>Player is already blacklisted.')->go();
		}

		$db->insert('message_blacklist', $params);

		new MessageBlacklist($blacklisted->getDisplayName() . ' has been added to your blacklist.')->go();
	}

}
