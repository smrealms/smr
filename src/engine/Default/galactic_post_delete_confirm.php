<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (isset($var['article'])) {
	$template->assign('PageTopic', 'Delete Article - Confirm');
	$dbResult = $db->read('SELECT title FROM galactic_post_article WHERE article_id = ' . $db->escapeNumber($var['id']) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$template->assign('ArticleTitle', $dbResult->record()->getField('title'));
	$container = Page::create('galactic_post_delete_processing.php');
	$container->addVar('article');
	$container->addVar('id');
	$template->assign('SubmitHREF', $container->href());
} else {
	// Delete paper
	$template->assign('PageTopic', 'Delete Paper - Confirm');
	$dbResult = $db->read('SELECT title FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
	$template->assign('PaperTitle', $dbResult->record()->getField('title'));

	$articles = [];
	$dbResult = $db->read('SELECT title FROM galactic_post_paper_content JOIN galactic_post_article USING (game_id, article_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
	foreach ($dbResult->records() as $dbRecord) {
		$articles[] = bbifyMessage($dbRecord->getField('title'));
	}
	$template->assign('Articles', $articles);

	$container = Page::create('galactic_post_delete_processing.php');
	$container->addVar('paper');
	$container->addVar('id');
	$template->assign('SubmitHREF', $container->href());
}
