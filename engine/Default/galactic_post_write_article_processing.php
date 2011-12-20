<?php

$title = trim($_REQUEST['title']);
$message = trim($_REQUEST['message']);
if(!isset($var['id'])) {
	$title = htmlentities($title,ENT_COMPAT,'utf-8');
	$message = htmlentities($message,ENT_COMPAT,'utf-8');
}

if($_REQUEST['action'] == 'Preview article') {
	$container = create_container('skeleton.php','galactic_post_write_article.php');
	$container['PreviewTitle'] = $title;
	$container['Preview'] = $message;
	if(isset($var['id'])) {
		$container['id'] = $var['id'];
	}
	forward($container);
}

if (empty($title)) {
	create_error('You must enter a title.');
}
if(empty($message)) {
	create_error('You must enter some text.');
}

if(isset($var['id'])) {
	$db->query('UPDATE galactic_post_article SET last_modified = ' . TIME . ', text = '.$db->escapeString($message).', title = '.$db->escapeString($title).' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = '.$var['id']);
	//its been changed send back now
	forward(create_container('skeleton.php','galactic_post_view_article.php'));
}
else {
	$db->query('SELECT MAX(article_id) article_id FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' LIMIT 1');
	$db->nextRecord();
	$num = $db->getField('article_id') + 1;
	$db->query('INSERT INTO galactic_post_article (game_id, article_id, writer_id, title, text, last_modified) VALUES (' . $db->escapeNumber($player->getGameID()) . ', '.$num.', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($title) . ' , ' . $db->escapeString($message) . ' , ' . TIME . ')');
	$db->query('UPDATE galactic_post_writer SET last_wrote = ' . TIME . ' WHERE account_id = '.$account->getAccountID());
	forward(create_container('skeleton.php', 'galactic_post_read.php'));
}
?>