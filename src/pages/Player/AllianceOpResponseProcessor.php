<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$response = strtoupper(Request::get('op_response'));

		$db = Database::getInstance();
		$db->replace('alliance_has_op_response', [
			'alliance_id' => $db->escapeNumber($player->getAllianceID()),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'response' => $db->escapeString($response),
		]);

		Page::create('alliance_mod.php')->go();
