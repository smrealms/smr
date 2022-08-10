<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

		$session = Smr\Session::getInstance();
		$account = $session->getAccount();

		if (Request::get('action') == 'Yes') {
			$db = Database::getInstance();
			$db->write('DELETE
						FROM album
						WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));

			$db->write('DELETE
						FROM album_has_comments
						WHERE album_id = ' . $db->escapeNumber($account->getAccountID()));
		}

		if ($session->hasGame()) {
			$container = Page::create('current_sector.php');
		} else {
			$container = Page::create('game_play.php');
		}

		$container->go();
