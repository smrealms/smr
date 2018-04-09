<?php
$db2 = new SmrMySqlDatabase();
if (isset($var['article'])) {
	$db->query('DELETE FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = '.$db->escapeNumber($var['id']));
}
else {
	//we are deleting an entire paper
	//find out which articles need to be deleted as well
	$db->query('SELECT * FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = '.$db->escapeNumber($var['id']));
	while($db->nextRecord()) {
		//delete this article that is part of this paper
		$db2->query('DELETE FROM galactic_post_article WHERE article_id = ' . $db->escapeNumber($db->getInt('article_id')) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	}

	//we have deleted the articles now delete the paper
	$db->query('DELETE FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = '.$db->escapeNumber($var['id']));
	//now delete form the content table
	$db->query('DELETE FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = '.$db->escapeNumber($var['id']));
}

$container = create_container('skeleton.php', 'galactic_post.php');
forward($container);
