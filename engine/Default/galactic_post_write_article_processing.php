<?php

$title = htmlentities(trim($_REQUEST['title']));
$message = htmlentities(trim($_REQUEST['message']));

if($_REQUEST['action'] == 'Preview article')
{
	$container = create_container('skeleton.php','galactic_post_write_article.php');
	$container['previewTitle'] = $title;
	$container['preview'] = $message;
	forward($container);
}

if (empty($title))
{
    create_error('You must enter a title');
}
if(empty($message))
{
    create_error('You must enter some text');
}
$db->query('SELECT MAX(article_id) article_id FROM galactic_post_article WHERE game_id = '.$player->getGameID().' LIMIT 1');
$db->nextRecord();
$num = $db->getField('article_id') + 1;
$db->query('INSERT INTO galactic_post_article (game_id, article_id, writer_id, title, text, last_modified) VALUES ('.$player->getGameID().', '.$num.', '.$player->getAccountID().', ' . $db->escapeString($title) . ' , ' . $db->escapeString($message) . ' , ' . TIME . ')');
$db->query('UPDATE galactic_post_writer SET last_wrote = ' . TIME . ' WHERE account_id = '.$account->getAccountID());
forward(create_container('skeleton.php', 'galactic_post_read.php'));

?>