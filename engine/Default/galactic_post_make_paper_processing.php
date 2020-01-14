<?php declare(strict_types=1);

$db->query('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY paper_id DESC');
if ($db->nextRecord()) {
	$num = $db->getInt('paper_id') + 1;
} else {
	$num = 1;
}
$title = Request::get('title');
$db->query('INSERT INTO galactic_post_paper (game_id, paper_id, title) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($num) . ', ' . $db->escapeString($title) . ')');
//send em back
$container = create_container('skeleton.php', 'galactic_post_view_article.php');
forward($container);
