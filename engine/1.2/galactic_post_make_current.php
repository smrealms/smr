<?php

//check to see if there is a paper already online
$db->query("SELECT * FROM galactic_post_online WHERE game_id = $player->game_id");
if ($db->next_record())
    $del_paper_id = $db->f("paper_id");
if (!empty($del_paper_id ))
    $db->query("DELETE FROM galactic_post_online WHERE paper_id = $del_paper_id");

//insert the new paper in.
$db->query("INSERT INTO galactic_post_online (paper_id, game_id, online_since) VALUES ($var[id], $player->game_id, " . time() . " )");
//all done lets send back to the main GP page.
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "galactic_post.php";
forward($container);

?>