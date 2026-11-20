<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class NpcManageUpdateProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $playerID,
		private readonly int $gameID,
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();
		if (Request::has('active-submit')) {
			$db->update(
				'npc_players',
				['active' => $db->escapeBoolean(Request::has('active'))],
				['player_id' => $this->playerID],
			);
		}
		if (Request::has('lock_ship-submit')) {
			$db->update(
				'npc_players',
				['lock_ship' => $db->escapeBoolean(Request::has('lock_ship'))],
				['player_id' => $this->playerID],
			);
		}
		new NpcManage($this->gameID)->go();
	}

}
