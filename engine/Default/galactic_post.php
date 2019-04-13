<?php

if (!$player->isGPEditor()) {
	throw new Exception('Only the GP Editor is allowed to view this page!');
}

$template->assign('PageTopic','Galactic Post');
Menu::galactic_post();

$db2 = new SmrMySqlDatabase();

$container = create_container('skeleton.php', 'galactic_post_view_article.php');
$template->assign('ViewArticlesHREF', SmrSession::getNewHREF($container));
$container['body'] = 'galactic_post_make_paper.php';
$template->assign('MakePaperHREF', SmrSession::getNewHREF($container));

$db->query('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
$papers = [];
while ($db->nextRecord()) {
	$paper_name = $db->getField('title');
	$paper_id = $db->getInt('paper_id');
	$published = $db->getInt('online_since');

	$db2->query('SELECT count(*) FROM galactic_post_paper_content WHERE paper_id = ' . $db2->escapeNumber($paper_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$db2->nextRecord();
	$numArticles = $db2->getField('count(*)');
	$hasEnoughArticles = $numArticles > 2 && $numArticles < 9;

	$paper = [
		'title' => $db->getField('title'),
		'num_articles' => $numArticles,
		'color' => $hasEnoughArticles ? 'green' : 'red',
		'published' => !empty($published) && $published > 0,
	];

	if (!empty($published)) {
	} else if ($hasEnoughArticles) {
		$container = create_container('galactic_post_make_current.php');
		$container['id'] = $paper_id;
		$paper['PublishHREF'] = SmrSession::getNewHREF($container);
	}

	$container = create_container('skeleton.php', 'galactic_post_delete_confirm.php');
	$container['paper'] = 'yes';
	$container['id'] = $paper_id;
	$paper['DeleteHREF'] = SmrSession::getNewHREF($container);

	$container = create_container('skeleton.php', 'galactic_post_paper_edit.php');
	$container['id'] = $paper_id;
	$paper['EditHREF'] = SmrSession::getNewHREF($container);

	$papers[] = $paper;
}
$template->assign('Papers', $papers);
