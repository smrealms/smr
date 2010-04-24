<?php
require_once(get_file_loc('smr_sector.inc'));

// register game_id
SmrSession::$game_id = $var["game_id"];

// check if hof entry is there
$db->query("SELECT * FROM account_has_stats WHERE account_id = ".SmrSession::$old_account_id);
if (!$db->nf())
	$db->query("INSERT INTO account_has_stats (account_id, HoF_name, games_joined) VALUES ($account->account_id, " . format_string($account->login, true) . ", 1)");

$player = new SMR_PLAYER(SmrSession::$old_account_id, $var["game_id"]);
include(get_file_loc('out_check.php'));
$player->last_sector_id = 0;
$player->last_active = time();
$player->update();

// get rid of old plotted course
$player->delete_plotted_course();

// log
$account->log(2, "Player entered game ".SmrSession::$game_id, $player->sector_id);

$container = array();
$container["url"] = "skeleton.php";
if ($player->land_on_planet == "TRUE")
    $container["body"] = "planet_main.php";
else
    $container["body"] = "current_sector.php";

	require_once(get_file_loc('smr_alliance.inc'));
	require_once(get_file_loc("smr_force.inc"));
	require_once(get_file_loc("smr_planet.inc"));
	require_once(get_file_loc("smr_port.inc"));
	require_once(get_file_loc('smr_sector.inc'));

	$player	= new SMR_PLAYER(SmrSession::$old_account_id, SmrSession::$game_id);
	$ship	= new SMR_SHIP(SmrSession::$old_account_id, SmrSession::$game_id);
	$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

	// update turns on that player
	$player->update_turns($ship->speed);

	// we cant move if we are dead
	//check if we are in kill db...if we are we don't do anything
	$db->query("SELECT * FROM kills WHERE dead_id = $player->account_id AND game_id = $player->game_id");
	if (!$db->next_record()) {

		if ($ship->hardware[HARDWARE_SHIELDS] == 0 && $ship->hardware[HARDWARE_ARMOR] == 0 && ($var["body"] != "trader_attack.php" && $var["url"] != "trader_attack_processing.php" && $var["body"] != "port_attack.php" && $var["url"] != "port_attack_processing.php"&& $var["body"] != "planet_attack.php" && $var["url"] != "planet_attack_processing.php")) {

			$player->sector_id = $player->get_home();
			$player->newbie_turns = 100;
			$player->mark_dead();
			$player->update();
			$ship->get_pod();
			//print("$var[body], $var[url]");
			$container = array();
			$container["url"] = "skeleton.php";
			$container["body"] = "current_sector.php";
			forward($container);

		}
	} elseif (!isset($var["ahhh"])) {

		$db->query("SELECT * FROM kills WHERE dead_id = $player->account_id AND processed = 'TRUE' AND game_id = $player->game_id");
		if ($db->next_record() && $var["body"] != "trader_attack.php") {

			$container = array();
			$container["url"] = "skeleton.php";
			$container["body"] = "death.php";
			$container["ahhh"] = "Yes";
			forward($container);

		}
	}

	if ($player->newbie_turns <= 20 &&
		$player->newbie_warning == "TRUE" &&
		$var["body"] != "newbie_warning.php")
		forward(create_container("skeleton.php", "newbie_warning.php"));

forward($container);

?>