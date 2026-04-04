<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class MessageBlacklistAddProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly ?int $blacklistAccountID = null,
	) {}

	public function build(AbstractPlayer $player): never {
		if ($this->blacklistAccountID !== null) {
			$blacklisted = Player::getPlayer($this->blacklistAccountID, $player->getGameID());
		} else {
			try {
				$blacklisted = Player::getPlayerByPlayerName(Request::get('PlayerName'), $player->getGameID());
			} catch (PlayerNotFound) {
				$container = new MessageBlacklist('<span class="red bold">ERROR: </span>Player does not exist.');
				$container->go();
			}
		}

		$db = Database::getInstance();
		$params = [
			...$player->SQLID,
			'blacklisted_id' => $blacklisted->getAccountID(),
		];
		$dbResult = $db->select('message_blacklist', $params);

		if ($dbResult->hasRecord()) {
			$container = new MessageBlacklist('<span class="red bold">ERROR: </span>Player is already blacklisted.');
			$container->go();
		}

		$db->insert('message_blacklist', $params);

		$container = new MessageBlacklist($blacklisted->getDisplayName() . ' has been added to your blacklist.');
		$container->go();
	}

}
