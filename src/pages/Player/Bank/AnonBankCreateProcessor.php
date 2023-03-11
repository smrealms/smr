<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AnonBankCreateProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$password = Request::get('password');

		if (empty($password)) {
			create_error('You cannot use a blank password!');
		}

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT IFNULL(MAX(anon_id), 0) as max_id FROM anon_bank WHERE game_id = :game_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$nextID = $dbResult->record()->getInt('max_id') + 1;

		$db->insert('anon_bank', [
			'game_id' => $player->getGameID(),
			'anon_id' => $nextID,
			'owner_id' => $player->getAccountID(),
			'password' => $password,
			'amount' => 0,
		]);

		$message = '<p>Account #' . $nextID . ' has been opened for you.</p>';
		$container = new AnonBank($message);
		$container->go();
	}

}
