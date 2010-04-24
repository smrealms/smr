<?php

$value = strtoupper($_POST["action"]);

$db->query("UPDATE player SET ignore_global = '$value' WHERE game_id = $player->game_id AND account_id = $player->account_id");

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "message_view.php";
forward($container);

?>