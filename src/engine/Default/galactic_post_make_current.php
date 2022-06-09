<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

// Make sure this paper hasn't been published before
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT 1 FROM galactic_post_paper WHERE online_since IS NOT NULL AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
if ($dbResult->hasRecord()) {
	create_error('Cannot publish a paper that has previously been published!');
}

// Update the online_since column
$db->write('UPDATE galactic_post_paper SET online_since=' . $db->escapeNumber(Smr\Epoch::time()) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));

//all done lets send back to the main GP page.
$container = Page::create('galactic_post.php');
$container->go();
