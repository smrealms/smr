<?php

$db->query("DELETE FROM galactic_post_paper_content WHERE game_id = $player->game_id AND article_id = $var[article_id]");

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "galactic_post_paper_edit.php";
transfer("id");
forward($container);

?>