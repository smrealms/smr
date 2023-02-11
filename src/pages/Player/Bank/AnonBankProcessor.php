<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AnonBankProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$account_num = Request::getInt('account_num');

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT password
			FROM anon_bank
			WHERE anon_id = :anon_id
			AND game_id = :game_id', [
			'anon_id' => $db->escapeNumber($account_num),
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		if (!$dbResult->hasRecord()) {
			create_error('This anonymous account does not exist!');
		}
		$dbRecord = $dbResult->record();

		if ($dbRecord->getString('password') != Request::get('password')) {
			create_error('Invalid anonymous account password!');
		}

		$container = new AnonBankDetail($account_num);
		$container->go();
	}

}
