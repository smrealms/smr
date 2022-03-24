<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

Menu::galacticPost();

if (!empty($var['paper_id'])) {
	if (!isset($var['game_id'])) {
		create_error('Must specify a game ID!');
	}

	// Create link back to past editions
	if (isset($var['back']) && $var['back']) {
		$container = Page::create('skeleton.php', 'galactic_post_past.php');
		$container->addVar('game_id');
		$template->assign('BackHREF', $container->href());
	}

	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT title FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($var['game_id']) . ' AND paper_id = ' . $var['paper_id']);
	$paper_name = bbifyMessage($dbResult->record()->getString('title'));
	$template->assign('PageTopic', 'Reading <i>Galactic Post</i> Edition : ' . $paper_name);

	//now get the articles in this paper.
	$dbResult = $db->read('SELECT * FROM galactic_post_paper_content JOIN galactic_post_article USING(game_id, article_id) WHERE paper_id = ' . $db->escapeNumber($var['paper_id']) . ' AND game_id = ' . $db->escapeNumber($var['game_id']));

	$articles = [];
	foreach ($dbResult->records() as $dbRecord) {
		$articles[] = [
			'title' => $dbRecord->getString('title'),
			'text' => $dbRecord->getString('text'),
		];
	}

	// Determine the layout of the articles on the page
	$articleLayout = [];
	$row = 0;
	foreach ($articles as $i => $article) {
		$articleLayout[$row][] = $article;

		// start a new row every 2 articles
		if ($i % 2 == 1) {
			$row++;
		}
	}
	$template->assign('ArticleLayout', $articleLayout);
} else {
	$template->assign('PageTopic', 'Galactic Post');
}
