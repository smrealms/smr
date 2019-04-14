<?php
$template->assign('PageTopic','Viewing Articles');
Menu::galactic_post();

if (isset($var['news'])) {
	$db->query('INSERT INTO news (game_id, time, news_message, type) ' .
		'VALUES(' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber(TIME) . ', ' . $db->escapeString($var['news']) . ', \'BREAKING\')');
	// avoid multiple insertion on ajax updates
	SmrSession::updateVar('news', null);
	SmrSession::updateVar('added_to_breaking_news', true);
}

// Get the articles that are not already in a paper
$articles = [];
$db->query('SELECT * FROM galactic_post_article WHERE article_id NOT IN (SELECT article_id FROM galactic_post_paper_content) AND game_id = ' . $db->escapeNumber($player->getGameID()));
while ($db->nextRecord()) {
	$title = stripslashes($db->getField('title'));
	$writer = SmrPlayer::getPlayer($db->getField('writer_id'), $player->getGameID());
	$container = create_container('skeleton.php', 'galactic_post_view_article.php');
	$container['id'] = $db->getField('article_id');
	$articles[] = [
		'title' => $title,
		'writer' => $writer->getPlayerName(),
		'link' => SmrSession::getNewHREF($container),
	];
}
$template->assign('Articles', $articles);

// Details about a selected article
if (isset($var['id'])) {
	$db->query('SELECT * FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = '.$db->escapeNumber($var['id']));
	$db->nextRecord();

	$container = create_container('skeleton.php', 'galactic_post_write_article.php');
	transfer('id');
	$editHREF = SmrSession::getNewHREF($container);

	$container = create_container('skeleton.php', 'galactic_post_delete_confirm.php');
	$container['article'] = 'yes';
	transfer('id');
	$deleteHREF = SmrSession::getNewHREF($container);

	$selectedArticle = [
		'title' => stripslashes($db->getField('title')),
		'text' => stripslashes($db->getField('text')),
		'editHREF' => $editHREF,
		'deleteHREF' => $deleteHREF,
	];
	$template->assign('SelectedArticle', $selectedArticle);

	$container = create_container('galactic_post_add_article_to_paper.php');
	transfer('id');
	$papers = [];
	$db->query('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
	while ($db->nextRecord()) {
		$container['paper_id'] = $db->getField('paper_id');
		$papers[] = [
			'title' => $db->getField('title'),
			'addHREF' => SmrSession::getNewHREF($container),
		];
	}
	$template->assign('Papers', $papers);

	if (empty($papers)) {
		$container = create_container('skeleton.php', 'galactic_post_make_paper.php');
		$template->assign('MakePaperHREF', SmrSession::getNewHREF($container));
	}

	// breaking news options
	$template->assign('AddedToNews', $var['added_to_breaking_news'] ?? false);
	if (empty($var['added_to_breaking_news'])) {
		$container = create_container('skeleton.php', 'galactic_post_view_article.php');
		$container['news'] = $selectedArticle['text'];
		transfer('id');
		$template->assign('AddToNewsHREF', SmrSession::getNewHREF($container));
	}
}
