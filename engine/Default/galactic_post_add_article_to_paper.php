<?php

//limit 4 per paper...make sure we arent over that
$db->query('SELECT * FROM galactic_post_paper_content WHERE game_id = '.$player->getGameID().' AND paper_id = '.$var['paper_id']);
if ($db->getNumRows() >= 8) {
	create_error('You can only have 8 articles per paper.');
}
$db->query('INSERT INTO galactic_post_paper_content (game_id, paper_id, article_id) VALUES ('.$player->getGameID().', '.$var['paper_id'].', '.$var['id'].')');
//we now have that article in the paper
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'galactic_post_view_article.php';
forward($container);

?>