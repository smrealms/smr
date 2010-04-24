<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);
		require_once(get_file_loc("smr_planet.inc"));
print_topic("VIEWING PLANET DETAILS");
$db2 = new SmrMySqlDatabase();
if ($player->alliance_id != 0)
	$db->query("SELECT planet.sector_id as sector, player.game_id as game, time_attack, attacker_damage, planet_damage, trigger_id FROM player, planet, planet_attack WHERE player.game_id = planet.game_id AND " .
											  "owner_id = account_id AND " .
											  "player.game_id = $player->game_id AND " .
											  "planet.game_id = $player->game_id AND " .
                                              "planet.sector_id = planet_attack.sector_id AND " .
                                              "planet.game_id = planet_attack.game_id AND " .
											  "alliance_id = $player->alliance_id " .
										"ORDER BY time_attack DESC");
else
	$db->query("SELECT planet.sector_id as sector, player.game_id as game, time_attack, attacker_damage, planet_damage, trigger_id FROM player, planet, planet_attack WHERE player.game_id = planet.game_id AND " .
    										"owner_id = account_id AND " .
                                            "planet.game_id = $player->game_id AND " .
                                            "planet.sector_id = planet_attack.sector_id AND " .
                                            "planet.game_id = planet_attack.game_id AND " .
                                            "player.game_id = $player->game_id " .
                                      "ORDER BY time_attack DESC");

while ($db->next_record()) {

	$game_id = $db->f("game");
    $sector_id = $db->f("sector");
	$time = $db->f("time_attack");
    $attacker = new SMR_PLAYER($db->f("trigger_id"), $player->game_id);
	$att_damage = $db->f("attacker_damage");
	$planet_damage = $db->f("planet_damage");
	$planet = new SMR_PLANET($db->f("sector"), $player->game_id);
	print("Planet <span style=font-variant:small-caps>$planet->planet_name</span> is under attack by " . $attacker->get_colored_name() . "<br>");
	print("This shot was at " . date("n/j/Y g:i:s A", $time) . ".  The attacking team did $att_damage damage ");
	print("while the planet did $planet_damage damage<br><br>");

}

?>