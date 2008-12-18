<?
$text = $_REQUEST['text'];
$title = $_REQUEST['title'];
$db->query('UPDATE galactic_post_article SET last_modified = ' . TIME . ', text = '.$db->escapeString($text).', title = '.$db->escapeString($title).' WHERE game_id = '.SmrSession::$game_id.' AND article_id = '.$var['id']);
//its been changed send back now
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'galactic_post_view_article.php';
forward($container);

?>