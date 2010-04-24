<?php

$db->query("SELECT * FROM galactic_post_paper WHERE game_id = $player->game_id ORDER BY paper_id DESC");
if ($db->next_record())
    $num = $db->f("paper_id") + 1;
else
    $num = 1;
$title = $_REQUEST['title'];
$db->query("INSERT INTO galactic_post_paper (game_id, paper_id, title) VALUES ($player->game_id, $num, '$title')");
//send em back
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "galactic_post_view_article.php";
forward($container);

?>