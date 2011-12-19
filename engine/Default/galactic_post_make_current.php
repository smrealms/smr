<?php

//check to see if there is a paper already online
$db->query('SELECT * FROM galactic_post_online WHERE game_id = '.$player->getGameID());
if ($db->nextRecord()) {
	$del_paper_id = $db->getField('paper_id');
}
if (!empty($del_paper_id )) {
	$db->query('DELETE FROM galactic_post_online WHERE paper_id = '.$del_paper_id);
}

//insert the new paper in.
$db->query('INSERT INTO galactic_post_online (paper_id, game_id, online_since) VALUES ('.$var['id'].', '.$player->getGameID().', ' . TIME . ' )');
//all done lets send back to the main GP page.
$container = create_container('skeleton.php', 'galactic_post.php');
forward($container);

?>