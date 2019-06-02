<?php

if (isset($var['article'])) {
	$template->assign('PageTopic', 'Delete Article - Confirm');
	$db->query('SELECT * FROM galactic_post_article WHERE article_id = ' . $db->escapeNumber($var['id']) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$db->nextRecord();
	$template->assign('ArticleTitle', $db->getField('title'));
	$container = create_container('galactic_post_delete.php');
	transfer('article');
	transfer('id');
	$template->assign('SubmitHREF', SmrSession::getNewHREF($container));
}
else {
	// Delete paper
	$template->assign('PageTopic', 'Delete Paper - Confirm');
	$db->query('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
	$db->nextRecord();
	$template->assign('PaperTitle', $db->getField('title'));

	$articles = [];
	$db->query('SELECT title FROM galactic_post_paper_content JOIN galactic_post_article USING (game_id, article_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
	while ($db->nextRecord()) {
		$articles[] = bbifyMessage($db->getField('title'));
	}
	$template->assign('Articles', $articles);

	$container = create_container('galactic_post_delete.php');
	transfer('paper');
	transfer('id');
	$template->assign('SubmitHREF', SmrSession::getNewHREF($container));
}
