<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Viewing Articles');
Menu::galactic_post();

if (isset($var['news'])) {
	$db->query('INSERT INTO news (game_id, time, news_message, type) ' .
		'VALUES(' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber(Smr\Epoch::time()) . ', ' . $db->escapeString($var['news']) . ', \'BREAKING\')');
	// avoid multiple insertion on ajax updates
	$session->updateVar('news', null);
	$session->updateVar('added_to_breaking_news', true);
}

// Get the articles that are not already in a paper
$articles = [];
$db->query('SELECT * FROM galactic_post_article WHERE article_id NOT IN (SELECT article_id FROM galactic_post_paper_content) AND game_id = ' . $db->escapeNumber($player->getGameID()));
while ($db->nextRecord()) {
	$title = stripslashes($db->getField('title'));
	$writer = SmrPlayer::getPlayer($db->getInt('writer_id'), $player->getGameID());
	$container = Page::create('skeleton.php', 'galactic_post_view_article.php');
	$container['id'] = $db->getInt('article_id');
	$articles[] = [
		'title' => $title,
		'writer' => $writer->getDisplayName(),
		'link' => $container->href(),
	];
}
$template->assign('Articles', $articles);

// Details about a selected article
if (isset($var['id'])) {
	$db->query('SELECT * FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['id']));
	$db->requireRecord();

	$container = Page::create('skeleton.php', 'galactic_post_write_article.php');
	$container->addVar('id');
	$editHREF = $container->href();

	$container = Page::create('skeleton.php', 'galactic_post_delete_confirm.php');
	$container['article'] = 'yes';
	$container->addVar('id');
	$deleteHREF = $container->href();

	$selectedArticle = [
		'title' => stripslashes($db->getField('title')),
		'text' => stripslashes($db->getField('text')),
		'editHREF' => $editHREF,
		'deleteHREF' => $deleteHREF,
	];
	$template->assign('SelectedArticle', $selectedArticle);

	$container = Page::create('galactic_post_add_article_to_paper.php');
	$container->addVar('id');
	$papers = [];
	$db->query('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
	while ($db->nextRecord()) {
		$container['paper_id'] = $db->getInt('paper_id');
		$papers[] = [
			'title' => $db->getField('title'),
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
