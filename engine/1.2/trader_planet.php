<?php		
require_once(get_file_loc('smr_alliance.inc'));
		require_once(get_file_loc("smr_planet.inc"));
function print_time($sek) {

	$i = sprintf('%d:%d:%d ',
				 $sek / 3600 % 24,
				 $sek / 60 % 60,
				 $sek % 60);
	return $i;
}

print_topic("PLANETS");

include(get_file_loc('menue.inc'));
print_trader_menue();

$db2 = new SmrMySqlDatabase();
$db->query("SELECT * FROM player, planet WHERE player.account_id = planet.owner_id AND " .
											  "player.game_id = $player->game_id AND " .
											  "planet.game_id = $player->game_id AND " .
											  "player.account_id = $player->account_id");
print_topic("YOUR PLANET");
if ($db->nf() > 0) {

	print("<div align=\"center\">");
	print("<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\" width=\"95%\">");
	print("<tr>");
	print("<th align=\"center\">Name</th>");
	print("<th align=\"center\">Sector</th>");
	print("<th align=\"center\">Galaxy</th>");
	print("<th align=\"center\">G</th>");
	print("<th align=\"center\">H</th>");
	print("<th align=\"center\">T</th>");
	print("<th align=\"center\">Build</th>");
	print("<th align=\"center\">Shields</th>");
	print("<th align=\"center\">Drones</th>");
	print("<th align=\"center\">Supplies</th>");
	print("</tr>");

	while ($db->next_record()) {

		$planet = new SMR_PLANET($db->f("sector_id"), SmrSession::$game_id);
		$planet_sector = new SMR_SECTOR($db->f("sector_id"), SmrSession::$game_id, SmrSession::$old_account_id);
		$planet_sec = $db->f("sector_id");
		print("<tr>");
		print("<td>$planet->planet_name</td>");
		print("<td align=\"right\">$planet->sector_id</td>");
		print("<td align=\"center\">$planet_sector->galaxy_name</td>");
		print("<td align=\"center\">" . $planet->construction[1] . "</td>");
		print("<td align=\"center\">" . $planet->construction[2] . "</td>");
		print("<td align=\"center\">" . $planet->construction[3] . "</td>");
		print("<td align=\"center\">");

		if ($planet->build()) {

			print("$planet->current_building_name<br>");
			print(print_time($planet->time_left));

		} else
			print("Nothing");

		print("</td>");
		print("<td align=\"center\">$planet->shields</td>");
		print("<td align=\"center\">$planet->drones</td>");
		print("<td align=\"left\">");
		foreach ($planet->stockpile as $id => $amount)

			if ($amount > 0) {

				$db2->query("SELECT * FROM good WHERE good_id = $id");
				if ($db2->next_record())
					print($db2->f("good_name") . ": $amount<br>");
				$supply = true;
			}

		if (!$supply)
			print("none");

	}

	print("</table>");
	print("</div>");

} else
	print("You don't have a planet claimed!<br><br>");
	
if ($player->alliance_id != 0) {
	
	$alliance = new SMR_ALLIANCE($player->alliance_id, SmrSession::$game_id);
	
	print_topic("PLANET LIST FOR $player->alliance_name ($player->alliance_id)");
	
	$db2 = new SmrMySqlDatabase();
	if (!isset($planet_sec)) $planet_sec = 0;
	$db->query("SELECT * FROM player, planet WHERE player.game_id = planet.game_id AND " .
												  "owner_id = account_id AND " .
												  "player.game_id = $player->game_id AND " .
												  "planet.game_id = $player->game_id AND " .
												  "planet.sector_id != $planet_sec AND " .
												  "alliance_id = $player->alliance_id " .
											"ORDER BY planet.sector_id");
	if ($db->nf() > 0) {
	
		print("<div align=\"center\">");
		print("<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\" width=\"95%\">");
		print("<tr>");
		print("<th align=\"center\">Name</th>");
		print("<th align=\"center\">Owner</th>");
		print("<th align=\"center\">Sector</th>");
		print("<th align=\"center\">Galaxy</th>");
		print("<th align=\"center\">G</th>");
		print("<th align=\"center\">H</th>");
		print("<th align=\"center\">T</th>");
		print("<th align=\"center\">Build</th>");
		print("<th align=\"center\">Shields</th>");
		print("<th align=\"center\">Drones</th>");
		print("<th align=\"center\">Supplies</th>");
		print("</tr>");
	
		while ($db->next_record()) {
	
			$planet = new SMR_PLANET($db->f("sector_id"), SmrSession::$game_id);
			$planet_sector = new SMR_SECTOR($db->f("sector_id"), SmrSession::$game_id, SmrSession::$old_account_id);
			$planet_owner = new SMR_PLAYER($planet->owner_id, SmrSession::$game_id);
			$planet->build();
			print("<tr>");
			print("<td>$planet->planet_name</td>");
			print("<td>$planet_owner->player_name</td>");
			print("<td align=\"center\">$planet->sector_id</td>");
			print("<td align=\"center\">$planet_sector->galaxy_name</td>");
			print("<td align=\"center\">" . $planet->construction[1] . "</td>");
			print("<td align=\"center\">" . $planet->construction[2] . "</td>");
			print("<td align=\"center\">" . $planet->construction[3] . "</td>");
			print("<td align=\"center\">");
	
			if ($planet->build()) {
	
				print("$planet->current_building_name<br>");
				print(print_time($planet->time_left));
	
			} else
				print("Nothing");
	
			print("</td>");
			print("<td align=\"center\">$planet->shields</td>");
			print("<td align=\"center\">$planet->drones</td>");
			print("<td align=\"left\">");
			$supply = false;
			foreach ($planet->stockpile as $id => $amount)
	
				if ($amount > 0) {
	
					$db2->query("SELECT * FROM good WHERE good_id = $id");
					if ($db2->next_record())
						print($db2->f("good_name") . ": $amount<br>");
					$supply = true;
				}
	
			if (!$supply)
				print("none");
			print("</td>");
		}
	
		print("</table>");
		print("</div>");
	
	} elseif ($planet_sec == 0)
		print("Your alliance has no claimed planets!");
	else
		print("Your planet is the only planet in the alliance!");

}

?>