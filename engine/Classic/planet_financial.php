<?php
		require_once(get_file_loc("smr_planet.inc"));
if ($player->land_on_planet == "FALSE") {

	print_error("You are not on a planet!");
	return;

}

// create planet object
$planet = new SMR_PLANET($player->sector_id, $player->game_id);
$planet->build();

print_topic("PLANET : $planet->planet_name [SECTOR #$player->sector_id]");

include(get_file_loc('menue.inc'));
print_planet_menue();

$curr_time = time();

$lvl = $planet->level();
if ($lvl < 9)
	$base = 1.02;
elseif ($lvl < 19)
	$base = 1.03;
elseif ($lvl < 29)
	$base = 1.06;
elseif ($lvl < 39)
	$base = 1.025;
elseif ($lvl < 49)
	$base = 1.02;
elseif ($lvl < 59)
	$base = 1.015;
elseif ($lvl < 69)
	$base = 1.01;
else
	$base = 1.009;

$rate = pow($base,2);

// grant for all days we didn't got
while ($planet->maturity < $curr_time && $planet->maturity > 0) {

	// calc the interest for the time
	$interest = $planet->bonds * $rate - $planet->bonds;

	// transfer money to free avail cash
	$planet->credits += $planet->bonds + $interest;

	// reset bonds
	$planet->bonds = 0;

	// reset maturity
	$planet->maturity = 0;

	// update planet
	$planet->update();

}

print("<p>Balance: <b>" . number_format($planet->credits) . "</b></p>");
if (!empty($interest))
	print("<span style=\"font-size:75%\">Since your last visit<br>you got <b>" . number_format(round($interest)) . "</b> credits interest!</span>");


print_form(create_container("planet_financial_processing.php", ""));
print("<table>");
print("<tr>");
print("<td colspan=\"2\" align=\"center\"><input type=\"text\" name=\"amount\" value=\"0\" id=\"InputFields\" style=\"text-align:right;width:152;\"></td>");
print("</tr>");
print("<tr>");
print("<td>");
print_submit("Deposit");
print("</td>");
print("<td>");
print_submit("Withdraw");
print("</td>");
print("</tr>");
print("</table>");

print("<p>&nbsp;</p>");

$bond_time = 48 / $player->game_speed;

print("<p>You are able to transfer this money into a saving bond.<br>");
print("It remains there for " . floor($bond_time) . " hours");
//php doesn't like floats with modulus operator...so we have to make them both integers with same ratio
//to be safe we will make it * 10000 (it allows us to have speeds like 1.2501)
$speed = $player->game_speed * 10000;
if (480000 % $speed != 0)
	print(", " . round(($bond_time - floor($bond_time)) * 60) . " minutes");

print(" and will gain " . ($rate * 100 - 100) . "% interest.<br><br>");

if ($planet->bonds > 0) {

	print("Right now there are " . number_format($planet->bonds) . " credits bonded");

	if ($planet->maturity > 0) {

		$maturity = $planet->maturity - $curr_time;
		$hours = floor($maturity / 3600);
		$minutes = ceil(($maturity - ($hours * 3600)) / 60);

		print(" and will come to maturity in ");
		if ($hours > 0) {

			if ($hours > 1)
				print("$hours hours");
			else
				print("1 hour");

			if ($minutes > 0)
				print(" and ");

		}

		if ($minutes > 0) {

			if ($minutes > 1)
				print("$minutes minutes");
			else
				print("1 minute");

		}

		print(".");

	}

}

print("</p>");

print_submit("Bond It!");

print("</form>");

?>