<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

//first check if there is a bar here
if (!$sector->has_bar()) create_error("So two guys walk into this bar...");

//get script to include
if (isset($var["script"])) $script = $var["script"];
else $script = "bar_opening.php";
//if ($script == 'bar_gambling_bet.php') create_error("Blackjack is currently outlawed, you will have to come back later.");
//get bar name
$db->query("SELECT location_name FROM location_type NATURAL JOIN location WHERE game_id = $player->game_id AND sector_id = $player->sector_id AND location_type.location_type_id > 800 AND location_type.location_type_id < 900");

//next welcome them
if ($db->next_record()) print_topic("Welcome to " . $db->f("location_name") . ".");
//in case for some reason there isn't a bar name found...should never happen but who knows
else print_topic("Welcome to this bar");

//include menu (not menue ;) )
include(get_file_loc('menue.inc'));
print_bar_menue();

//get rid of drinks older than 30 mins
$time = time() - 1800;
$db->query("DELETE FROM player_has_drinks WHERE time < $time");

//include bar part
include(get_file_loc("$script"));

?>