<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Viewing Articles');
Menu::galactic_post();

if (isset($var['news'])) {
	$db->write('INSERT INTO news (game_id, time, news_message, type) ' .
		'VALUES(' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber(Smr\Epoch::time()) . ', ' . $db->escapeString($var['news']) . ', \'BREAKING\')');
	// avoid multiple insertion on ajax updates
	$session->updateVar('news', null);
	$session->updateVar('added_to_breaking_news', true);
}

// Get the articles that are not already in a paper
$articles = [];
$dbResult = $db->read('SELECT * FROM galactic_post_article WHERE article_id NOT IN (SELECT article_id FROM galactic_post_paper_content) AND game_id = ' . $db->escapeNumber($player->getGameID()));
foreach ($dbResult->records() as $dbRecord) {
	$title = stripslashes($dbRecord->getField('title'));
	$writer = SmrPlayer::getPlayer($dbRecord->getInt('writer_id'), $player->getGameID());
	$container = Page::create('skeleton.php', 'galactic_post_view_article.php');
	$container['id'] = $dbRecord->getInt('article_id');
	$articles[] = [
		'title' => $title,
		'writer' => $writer->getDisplayName(),
		'link' => $container->href(),
	];
}
$template->assign('Articles', $articles);

// Details about a selected article
if (isset($var['id'])) {
	$dbResult = $db->read('SELECT * FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['id']));
	$dbRecord = $dbResult->record();

	$container = Page::create('skeleton.php', 'galactic_post_write_article.php');
	$container->addVar('id');
	$editHREF = $container->href();

	$container = Page::create('skeleton.php', 'galactic_post_delete_confirm.php');
	$container['article'] = 'yes';
	$container->addVar('id');
	$deleteHREF = $container->href();

	$selectedArticle = [
		'title' => stripslashes($dbRecord->getField('title')),
		'text' => stripslashes($dbRecord->getField('text')),
		'editHREF' => $editHREF,
		'deleteHREF' => $deleteHREF,
	];
	$template->assign('SelectedArticle', $selectedArticle);

	$container = Page::create('galactic_post_add_article_to_paper.php');
	$container->addVar('id');
	$papers = [];
	$dbResult = $db->read('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
	foreach ($dbResult->records() as $dbRecord) {
		$container['paper_id'] = $dbRecord->getInt('paper_id');
		$papers[] = [
			'title' => $dbRecord->getField('title'),
			'addHREF' => $container->href(),
		];
	}
	$template->assign('Papers', $papers);

	if (empty($papers)) {
		$container = Page::create('skeleton.php', 'galactic_post_make_paper.php');
		$template->assign('MakePaperHREF', $container->href());
	}

	// breaking news options
	$template->assign('AddedToNews', $var['added_to_breaking_news'] ?? false);
	if (empty($var['added_to_breaking_news'])) {
		$container = Page::create('skeleton.php', 'galactic_post_view_article.php');
		$container['news'] = $selectedArticle['text'];
		$container->addVar('id');
		$template->assign('AddToNewsHREF', $container->href());
	}
}
