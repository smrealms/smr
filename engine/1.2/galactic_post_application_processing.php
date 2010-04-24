<?php
$exp = $_REQUEST['exp'];
$message = $_REQUEST['message'];
$amount = $_REQUEST['amount'];
if (empty($exp) || empty($message) || empty($amount))
    create_error("You left some value blank");
if ($exp == 1)
    $value = "YES";
else
    $value = "NO";
$db->query("SELECT * FROM galactic_post_applications WHERE game_id = $player->game_id AND account_id = $player->account_id");
if ($db->next_record())
    create_error("You have already applied once.  Please be patient and your application will be answered at a later time.");

$db->query("REPLACE INTO galactic_post_applications (game_id, account_id, description, written_before, articles_per_day) VALUES (".SmrSession::$game_id.", $player->account_id, " . format_string("$message", true) . " , '$value', $amount)");
$container = array();
$container["url"] = "skeleton.php";
if ($player->land_on_planet == "FALSE")
    $container["body"] = "current_sector.php";
else
    $container["body"] = "planet_main.php";
$container["msg"] = "Thank you for your application.  It has been sent to the main editor and he will let you know if you have been accepted.";
forward($container);

?>