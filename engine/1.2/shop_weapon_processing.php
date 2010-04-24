<?php
$action = $_REQUEST['action'];
if ($action == 'Buy') {

	$cost = $var["cost"];
	$power_level = $var["power_level"];
	$cant_buy = $var["cant_buy"];
	// do we have enough cash?
	if ($player->credits < $cost)
		create_error("You do not have enough cash to purchase this weapon!");

	// can we load such a weapon (power_level)
	if ($ship->check_power_level($power_level) == 0)
		create_error("Your ship doesn't have enough power to support that weapon!");

	if ($cant_buy == "Yes")
    	create_error("We are at WAR!!! Do you really think I'm gonna sell you that weapon?");

    if ($ship->weapon_open < 1)
		create_error("You can't buy any more weapon!");

	if ($var["buyer_restriction"] == 2 && $player->alignment > -100)
		create_error("You can't buy evil weapons!");

	if ($var["buyer_restriction"] == 1 && $player->alignment < 100)
		create_error("You can't buy good weapons!");

	// take the money from the user
	$player->credits -= $cost;
	$player->update();

	// add the weapon to the users ship
	$ship->add_weapon($var["weapon_id"]);
	$db->query("SELECT * FROM weapon_type WHERE weapon_type_id = " . $var["weapon_type_id"]);
	$db->next_record();
	$wep_name = $db->f("weapon_name");
	$account->log(10, "Player Buys a $wep_name", $player->sector_id);

} elseif ($action == 'Sell') {

	// mhh we wonna sell our weapon

	// give the money to the user
	$player->credits += $var["cash_back"];
	$player->update();

	// take weapon
	unset($ship->weapon[$var["order_id"]]);

	// update
	$ship->update_weapon();

	$db->query("SELECT * FROM weapon_type WHERE weapon_type_id = " . $var["weapon_type_id"]);
	$db->next_record();
	$wep_name = $db->f("weapon_name");
	$account->log(10, "Player Sells a $wep_name", $player->sector_id);

}

forward(create_container("skeleton.php", "shop_weapon.php"));

?>