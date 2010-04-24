<?php

$db->query("UPDATE port SET race_id = $player->race_id WHERE game_id = $player->game_id AND sector_id = $player->sector_id");

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "port_loot.php";
forward($container);

?>