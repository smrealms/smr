<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class NpcManageAddAccountProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $selectedGameID
	) {}

	public function build(Account $account): never {
		// Add a new NPC account
		$login = Request::get('npc_login');
		$email = $login . '@smrealms.de';
		$npcAccount = Account::createAccount($login, '', $email, 0, 0);
		$npcAccount->setValidated(true);
		$npcAccount->update();

		$db = Database::getInstance();
		$db->insert('npc_logins', [
			'login' => $db->escapeString($login),
			'player_name' => $db->escapeString(Request::get('default_player_name')),
			'alliance_name' => $db->escapeString(Request::get('default_alliance')),
		]);

		$container = new NpcManage($this->selectedGameID);
		$container->go();
	}

}
