<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class MessageBlacklistDeleteProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$entry_ids = Request::getIntArray('entry_ids', []);
		if (empty($entry_ids)) {
			$container = new MessageBlacklist('<span class="red bold">ERROR: </span>No entries selected for deletion.');
			$container->go();
		}

		$db = Database::getInstance();
		$db->write('DELETE FROM message_blacklist WHERE account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND entry_id IN (' . $db->escapeArray($entry_ids) . ')');
		$container = new MessageBlacklist();
		$container->go();
	}

}
