<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$db = Smr\Database::getInstance();
if (isset($var['article'])) {
	if (Smr\Request::get('action') == 'Yes') {
		$db->write('DELETE FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['id']));
	}
} else {
	// Should we delete this paper?
	if (Smr\Request::get('action') == 'Yes') {

		// Should the articles associated with the paper be deleted as well?
		if (Smr\Request::get('delete_articles') == 'Yes') {
			$dbResult = $db->read('SELECT * FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
			foreach ($dbResult->records() as $dbRecord) {
				$db->write('DELETE FROM galactic_post_article WHERE article_id = ' . $db->escapeNumber($dbRecord->getInt('article_id')) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
			}
		}

		// Delete the paper and the article associations
		$db->write('DELETE FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
		$db->write('DELETE FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
	}
}

$container = Page::create('galactic_post.php');
$container->go();
