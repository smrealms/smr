<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM galactic_post_paper WHERE online_since IS NOT NULL AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY online_since DESC LIMIT 1');
if ($dbResult->hasRecord()) {
	$paper_id = $dbResult->record()->getInt('paper_id');
} else {
	$paper_id = null;
}

$container = Page::create('galactic_post_read.php');
$container['paper_id'] = $paper_id;
$container['game_id'] = $player->getGameID();
$container->go();
