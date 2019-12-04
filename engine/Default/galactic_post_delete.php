<?php declare(strict_types=1);
$db2 = new SmrMySqlDatabase();
if (isset($var['article'])) {
	if ($_REQUEST['action'] == 'Yes') {
		$db->query('DELETE FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['id']));
	}
} else {
	// Should we delete this paper?
	if ($_REQUEST['action'] == 'Yes') {

		// Should the articles associated with the paper be deleted as well?
		if ($_REQUEST['delete_articles'] == 'Yes') {
			$db->query('SELECT * FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
			while ($db->nextRecord()) {
				$db2->query('DELETE FROM galactic_post_article WHERE article_id = ' . $db->escapeNumber($db->getInt('article_id')) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
			}
		}

		// Delete the paper and the article associations
		$db->query('DELETE FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
		$db->query('DELETE FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
	}
}

$container = create_container('skeleton.php', 'galactic_post.php');
forward($container);
