<?php
$text = $_REQUEST['text'];
$title = $_REQUEST['title'];
$db->query("UPDATE galactic_post_article SET last_modified = " . time() . ", text = '$text', title = '$title' WHERE game_id = ".SmrSession::$game_id." AND article_id = $var[id]");
//its been changed send back now
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "galactic_post_view_article.php";
forward($container);

?>