<?
$title = $_REQUEST['title'];
$message = $_REQUEST['message'];
if (empty($title) || empty($message))
    create_error('You must enter some text or a title');
$db->query('SELECT * FROM galactic_post_article WHERE game_id = '.SmrSession::$game_id.' ORDER BY article_id DESC LIMIT 1');
$db->nextRecord();
$num = $db->getField('article_id') +1;
$db->query('INSERT INTO galactic_post_article (game_id, article_id, writer_id, title, text, last_modified) VALUES ('.$player->getGameID().', '.$num.', '.$player->getAccountID().', ' . $db->escape_string($title, FALSE) . ' , ' . $db->escape_string($message, false) . ' , ' . TIME . ')');
$db->query('UPDATE galactic_post_writer SET last_wrote = ' . TIME . ' WHERE account_id = '.$account->account_id);
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'galactic_post_read.php';
forward($container);

?>