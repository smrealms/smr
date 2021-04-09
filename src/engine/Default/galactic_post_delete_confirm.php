<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (isset($var['article'])) {
	$template->assign('PageTopic', 'Delete Article - Confirm');
	$db->query('SELECT * FROM galactic_post_article WHERE article_id = ' . $db->escapeNumber($var['id']) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$db->requireRecord();
	$template->assign('ArticleTitle', $db->getField('title'));
	$container = Page::create('galactic_post_delete_processing.php');
	$container->addVar('article');
	$container->addVar('id');
	$template->assign('SubmitHREF', $container->href());
} else {
	// Delete paper
	$template->assign('PageTopic', 'Delete Paper - Confirm');
	$db->query('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
	$db->requireRecord();
	$template->assign('PaperTitle', $db->getField('title'));

	$articles = [];
	$db->query('SELECT title FROM galactic_post_paper_content JOIN galactic_post_article USING (game_id, article_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
	while ($db->nextRecord()) {
		$articles[] = bbifyMessage($db->getField('title'));
	}
	$template->assign('Articles', $articles);

	$container = Page::create('galactic_post_delete_processing.php');
	$container->addVar('paper');
	$container->addVar('id');
	$template->assign('SubmitHREF', $container->href());
}
