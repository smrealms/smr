<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY paper_id DESC');
		if ($dbResult->hasRecord()) {
			$num = $dbResult->record()->getInt('paper_id') + 1;
		} else {
			$num = 1;
		}
		$title = Request::get('title');
		$db->insert('galactic_post_paper', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'paper_id' => $db->escapeNumber($num),
			'title' => $db->escapeString($title),
		]);
		//send em back
		$container = Page::create('galactic_post_view_article.php');
		$container->go();
