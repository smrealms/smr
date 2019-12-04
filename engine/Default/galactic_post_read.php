<?php declare(strict_types=1);

Menu::galactic_post();

if (!empty($var['paper_id'])) {
	if (!isset($var['game_id'])) {
		create_error('Must specify a game ID!');
	}

	// Create link back to past editions
	if (isset($var['back']) && $var['back']) {
		$container = create_container('skeleton.php', 'galactic_post_past.php');
		$container['game_id'] = $var['game_id'];
		$template->assign('BackHREF', SmrSession::getNewHREF($container));
	}

	$db->query('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($var['game_id']) . ' AND paper_id = ' . $var['paper_id']);
	$db->nextRecord();
	$paper_name = bbifyMessage($db->getField('title'));
	$template->assign('PageTopic', 'Reading <i>Galactic Post</i> Edition : ' . $paper_name);

	//now get the articles in this paper.
	$db->query('SELECT * FROM galactic_post_paper_content JOIN galactic_post_article USING(game_id, article_id) WHERE paper_id = ' . $db->escapeNumber($var['paper_id']) . ' AND game_id = ' . $db->escapeNumber($var['game_id']));

	$articles = [];
	while ($db->nextRecord()) {
		$articles[] = [
			'title' => $db->getField('title'),
			'text' => $db->getField('text'),
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
