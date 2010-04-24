<?php

print_topic("Beta things");

print("<b style=\"color:red;\">BIG WARNING! Be reasonable with he things you load to your ship! NEVER, i repeat NEVER (and it means NEVER) load more on a ship than suppossed to be on. NEVER give you more relation than 500. NEVER put you in a sector that doesn't exist! Don't say you havn't been warned! I WILL NOT fix your trader if you did!</b><br /><br />");

// container for all links
$container = create_container("beta_func_proc.php", "");

//first lets let them map all
$container["func"] = "Map";
print_link($container,"Map all");
print("<br>");

//next let them get money
$container["func"] = "Money";
print_link($container,"Load up the $$!!");
print("<br>");

//next time for ship
$container["func"] = "Ship";
print_form($container);
print("<select name=\"ship_id\">");
$db->query("SELECT * FROM ship_type ORDER BY ship_type_id");
while ($db->next_record())
	print("<option value=\"" . $db->f("ship_type_id") . "\">" . $db->f("ship_name") . "</option>");
print("</select>&nbsp;&nbsp;");
print_submit("Change Ship");
print("</form>");
print("<br>");

//next weapons
$container["func"] = "Weapon";
print_form($container);
print("Amount:&nbsp;&nbsp;<input type=\"text\" name=\"amount\" value=\"1\"><br>");
print("<select name=\"weapon_id\">");
$db->query("SELECT * FROM weapon_type ORDER BY weapon_type_id");
while ($db->next_record())
	print("<option value=\"" . $db->f("weapon_type_id") . "\">" . $db->f("weapon_name") . "</option>");
print("</select>&nbsp;&nbsp;");
print_submit("Add Weapon(s)");
print("</form>");

//Remove Weapons
$container["func"] = "RemWeapon";
print_link($container,"Remove Weapons");
print("<br>");

//allow to get full hardware
$container["func"] = "Uno";
print_link($container, "Get Full Hardware");

//move whereever u want
$container["func"] = "Warp";
print_form($container);
print("<input type=\"text\" name=\"sector_to\" value=\"$player->sector_id\">&nbsp;&nbsp;");
print_submit("Warp to Sector");
print("</form>");

//set experience
$container["func"] = "Exp";
print_form($container);
print("<input type=\"text\" name=\"exp\" value=\"$player->experience\">&nbsp;&nbsp;");
print_submit("Set Exp to Amount");
print("</form>");

//Set alignment
$container["func"] = "Align";
print_form($container);
print("<input type=\"text\" name=\"align\" value=\"$player->alignment\">&nbsp;&nbsp;");
print_submit("Set Align to Amount");
print("</form>");

$db->query("SELECT kills, experience_traded
			FROM account_has_stats
			WHERE account_id = ".SmrSession::$old_account_id);
if ($db->next_record()) {

	//Set kills
	$container["func"] = "Kills";
	print_form($container);
	print("<input type=\"text\" name=\"kills\" value=\"" . $db->f("kills") . "\">&nbsp;&nbsp;");
	print_submit("Set Kills to Amount");
	print("</form>");

	//Set traded xp
	$container["func"] = "Traded_XP";
	print_form($container);
	print("<input type=text name=\"traded_xp\" value=\"" . $db->f("experience_traded") . "\">&nbsp;&nbsp;");
	print_submit("Set Traded XP to Amount");
	print("</form>");

}

print("<br>Note: This sets your hardware not adds it. Also, if u have more than 1 JD,scanner,etc they may function incorrectly<br>");
//add any type of hardware
$container["func"] = "Hard_add";
print_form($container);
print("<input type=\"text\" name=\"amount_hard\" value=\"0\"><br>");
print("<select name=\"type_hard\">");
$db->query("SELECT * FROM hardware_type ORDER BY hardware_type_id");
while ($db->next_record()) {
	$id = $db->f("hardware_type_id");
	$name = $db->f("hardware_name");
	print("<option value=$id>$name</option>");
}
print("</select>&nbsp;&nbsp;");
print_submit("Set hardware");
print("</form>");
print("<br>Modify Personal Relations <small>note: DO NOT make this less than -500 or greater than 500!</small><br>");

//change personal relations
$container["func"] = "Relations";
$db->query("SELECT * FROM race WHERE race_id > 1 ORDER BY race_id");
print_form($container);
print("<select name=race>");
while ($db->next_record())
	print("<option value=\"" . $db->f("race_id") . "\">" . $db->f("race_name") . "</option>");
print("</select>&nbsp;&nbsp;");
print("<input name=\"amount\" value=\"0\">");
print_submit("Change Relations");
print("</form>");

print("<br>Modify Racial Relations <small>note: DO NOT make this less than -500 or greater than 500!</small><br>");

//change race relations
$container["func"] = "Race_Relations";
$db->query("SELECT * FROM race WHERE race_id > 1 ORDER BY race_id");
print_form($container);
print("<select name=\"race\">");
while ($db->next_record())
	print("<option value=\"" . $db->f("race_id") . "\">" . $db->f("race_name") . "</option>");
print("</select>&nbsp;&nbsp;");
print("<input name=\"amount\" value=\"0\">");
print_submit("Change Relations");
print("</form>");

?>