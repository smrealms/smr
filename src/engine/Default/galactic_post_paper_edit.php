<?php declare(strict_types=1);

$template->assign('PageTopic', 'Edit Paper');
Menu::galactic_post();

$db->query('SELECT * FROM galactic_post_paper WHERE paper_id = ' . $db->escapeNumber($var['id']) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
$db->requireRecord();
$template->assign('PaperTitle', bbifyMessage($db->getField('title')));

$db->query('SELECT * FROM galactic_post_paper_content JOIN galactic_post_article USING (game_id, article_id) WHERE paper_id = ' . $db->escapeNumber($var['id']) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));

$articles = [];
while ($db->nextRecord()) {
	$container = create_container('galactic_post_paper_edit_processing.php');
	$container['article_id'] = $db->getInt('article_id');
	transfer('id');
	$articles[] = [
		'title' => bbifyMessage($db->getField('title')),
		'text' => bbifyMessage($db->getField('text')),
		'editHREF' => SmrSession::getNewHREF($container),
	];
}
$template->assign('Articles', $articles);
