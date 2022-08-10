<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$password = Request::get('password');

		if (empty($password)) {
			create_error('You cannot use a blank password!');
		}

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT IFNULL(MAX(anon_id), 0) as max_id FROM anon_bank WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
		$nextID = $dbResult->record()->getInt('max_id') + 1;

		$db->insert('anon_bank', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'anon_id' => $db->escapeNumber($nextID),
			'owner_id' => $db->escapeNumber($player->getAccountID()),
			'password' => $db->escapeString($password),
			'amount' => 0,
		]);

		$container = Page::create('bank_anon.php');
		$container['message'] = '<p>Account #' . $nextID . ' has been opened for you.</p>';
		$container->go();
