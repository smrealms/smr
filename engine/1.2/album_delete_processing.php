<?php

if ($_POST["action"] == "Yes") {

	$db->query("DELETE
				FROM album
				WHERE account_id = " . SmrSession::$old_account_id);

	$db->query("DELETE
				FROM album_has_comments
				WHERE album_id = ". SmrSession::$old_account_id);

}

$container = array();
$container["url"] = "skeleton.php";
if ($player->land_on_planet == "TRUE")
    $container["body"] = "planet_main.php";
else
    $container["body"] = "current_sector.php";

forward($container);

?>