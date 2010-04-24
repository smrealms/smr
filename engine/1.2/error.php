<?php

if (empty($var["message"]) || $var["message"] == "") $var["message"] = "File not found";

if (SmrSession::$game_id > 0) {
	$container=array();
	$container['url'] = 'skeleton.php';
	if ($player->land_on_planet == "TRUE") $container['body'] = 'planet_main.php';
	else $container['body'] = 'current_sector.php';
	$errorMsg = "<span class=\"red bold\">ERROR:</span> " . $var['message'] . "!";
	$container['errorMsg'] = "$errorMsg";
	forward($container);
} else {
	print("<h1>ERROR</h1>");
	print("<p><b><big>" . $var["message"] . "</big></b></p>");
	print("<br><br><br>");
	print("<p><small>If the error was caused by something you entered, press back and try again.</small></p>");
	print("<p><small>If it was a DB Error, press back and try again, or logoff and log back on.</small></p>");
	print("<p><small>If the error was unrecognizable, please notify the administrators.</small></p>");
}
?>