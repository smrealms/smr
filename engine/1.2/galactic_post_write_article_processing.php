<?php
$title = $_REQUEST['title'];
$message = $_REQUEST['message'];
if (empty($title) || empty($message))
    create_error("You must enter some text or a title");
$db->query("SELECT * FROM galactic_post_article WHERE game_id = ".SmrSession::$game_id." ORDER BY article_id DESC LIMIT 1");
$db->next_record();
$num = $db->f("article_id") +1;
$db->query("INSERT INTO galactic_post_article (game_id, article_id, writer_id, title, text, last_modified) VALUES ($player->game_id, $num, $player->account_id, " . format_string("$title", FALSE) . " , " . format_string("$message", FALSE) . " , " . time() . ")");
$db->query("UPDATE galactic_post_writer SET last_wrote = " . time() . " WHERE account_id = $account->account_id");
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "galactic_post_read.php";
forward($container);

?>