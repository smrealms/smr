<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class MessageBlacklistDeleteProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$entry_ids = Request::getIntArray('entry_ids', []);
		if (count($entry_ids) === 0) {
			$container = new MessageBlacklist('<span class="red bold">ERROR: </span>No entries selected for deletion.');
			$container->go();
		}

		$db = Database::getInstance();
		$db->write('DELETE FROM message_blacklist WHERE account_id = :account_id AND entry_id IN (:entry_ids)', [
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'entry_ids' => $db->escapeArray($entry_ids),
		]);
		$container = new MessageBlacklist();
		$container->go();
	}

}
