<?php declare(strict_types=1);

use Smr\Database;
use Smr\News;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		$gameID = $var['GameID'] ?? $player->getGameID();

		$template->assign('PageTopic', 'Current News');
		Menu::news($gameID);

		News::doBreakingNewsAssign($gameID);
		News::doLottoNewsAssign($gameID);

		if (!isset($var['LastNewsUpdate'])) {
			$var['LastNewsUpdate'] = $player->getLastNewsUpdate();
		}

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND time > ' . $db->escapeNumber($var['LastNewsUpdate']) . ' AND type != \'lotto\' ORDER BY news_id DESC');
		$template->assign('NewsItems', News::getNewsItems($dbResult));

		$player->updateLastNewsUpdate();
