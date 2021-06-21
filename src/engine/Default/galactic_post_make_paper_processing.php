<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY paper_id DESC');
if ($dbResult->hasRecord()) {
	$num = $dbResult->record()->getInt('paper_id') + 1;
} else {
	$num = 1;
}
$title = Request::get('title');
$db->write('INSERT INTO galactic_post_paper (game_id, paper_id, title) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($num) . ', ' . $db->escapeString($title) . ')');
//send em back
$container = Page::create('skeleton.php', 'galactic_post_view_article.php');
$container->go();
