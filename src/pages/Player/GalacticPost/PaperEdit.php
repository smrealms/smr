<?php declare(strict_types=1);

use Smr\Database;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Edit Paper');
Menu::galacticPost();

$db = Database::getInstance();
$dbResult = $db->read('SELECT title FROM galactic_post_paper WHERE paper_id = ' . $db->escapeNumber($var['id']) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
$template->assign('PaperTitle', bbifyMessage($dbResult->record()->getString('title')));

$dbResult = $db->read('SELECT * FROM galactic_post_paper_content JOIN galactic_post_article USING (game_id, article_id) WHERE paper_id = ' . $db->escapeNumber($var['id']) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));

$articles = [];
foreach ($dbResult->records() as $dbRecord) {
	$container = Page::create('galactic_post_paper_edit_processing.php');
	$container['article_id'] = $dbRecord->getInt('article_id');
	$container->addVar('id');
	$articles[] = [
		'title' => bbifyMessage($dbRecord->getString('title')),
		'text' => bbifyMessage($dbRecord->getString('text')),
		'editHREF' => $container->href(),
	];
}
$template->assign('Articles', $articles);
