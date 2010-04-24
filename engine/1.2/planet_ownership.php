<?php
		require_once(get_file_loc("smr_planet.inc"));
if ($player->land_on_planet == "FALSE") {
	
	print_error("You are not on a planet!");
	return;
	
}

// create planet object
$planet = new SMR_PLANET($player->sector_id, $player->game_id);

print_topic("PLANET : $planet->planet_name [SECTOR #$player->sector_id]");

include(get_file_loc('menue.inc'));
print_planet_menue();

print("<p>");

if ($planet->owner_id == 0) {

	print("The planet is unclaimed.");
	print_form(create_container("planet_ownership_processing.php", ""));
	print_submit("Take Ownership");
	print("</form>");

} else {

	if ($planet->owner_id != $player->account_id) {

		print("You can claim the planet when you enter the correct password.");
		print_form(create_container("planet_ownership_processing.php", ""));
		print("<input type=\"text\" name=\"password\" id=\"InputFields\">&nbsp;&nbsp;&nbsp;");
		print_submit("Take Ownership");
		print("</form>");

	} else  {

		print("You can set a password for that planet.");
		print_form(create_container("planet_ownership_processing.php", ""));
		print("<input type=\"text\" name=\"password\" value=\"$planet->password\" id=\"InputFields\">&nbsp;&nbsp;&nbsp;");
		print_submit("Set Password");
		print("</form>");

		print("You can rename the planet.");
		print_form(create_container("planet_ownership_processing.php", ""));
		print("<input type=\"text\" name=\"name\" value=\"$planet->planet_name\" id=\"InputFields\">&nbsp;&nbsp;&nbsp;");
		print_submit("Rename");
		print("</form>");

	}
}

print("</p>");

?>