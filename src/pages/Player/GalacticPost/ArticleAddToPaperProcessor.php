<?php declare(strict_types=1);

use Smr\Database;

		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		//limit 4 per paper...make sure we arent over that
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['paper_id']));
		if ($dbResult->getNumRecords() >= 8) {
			create_error('You can only have 8 articles per paper.');
		}
		$db->insert('galactic_post_paper_content', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'paper_id' => $db->escapeNumber($var['paper_id']),
			'article_id' => $db->escapeNumber($var['id']),
		]);
		//we now have that article in the paper
		$container = Page::create('galactic_post_view_article.php');
		$container->go();
