<?php

// Make sure this paper hasn't been published before
$db->query('SELECT * FROM galactic_post_paper WHERE online_since IS NOT NULL AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));
if ($db->nextRecord()) {
	create_error("Cannot publish a paper that has previously been published!");
}

// Update the online_since column
$db->query('UPDATE galactic_post_paper SET online_since=' . $db->escapeNumber(TIME) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($var['id']));

//all done lets send back to the main GP page.
$container = create_container('skeleton.php', 'galactic_post.php');
forward($container);

?>
