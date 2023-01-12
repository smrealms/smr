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
		private readonly ?int $blacklistAccountID = null
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
		$dbResult = $db->read('SELECT 1 FROM message_blacklist WHERE ' . $player->getSQL() . ' AND blacklisted_id=' . $db->escapeNumber($blacklisted->getAccountID()) . ' LIMIT 1');

		if ($dbResult->hasRecord()) {
			$container = new MessageBlacklist('<span class="red bold">ERROR: </span>Player is already blacklisted.');
			$container->go();
		}

		$db->insert('message_blacklist', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'blacklisted_id' => $db->escapeNumber($blacklisted->getAccountID()),
		]);

		$container = new MessageBlacklist($blacklisted->getDisplayName() . ' has been added to your blacklist.');
		$container->go();
	}

}
