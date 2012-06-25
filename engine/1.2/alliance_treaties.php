<?php
//treaty info
$types = array	(
				"assistTrader" => array	(
										"Assist - Trader Attacks",
										"Assist your ally in attacking traders."
										),
				"assistRaids" => array	(
										"Assist - Planet & Port Attacks",
										"Assist your ally in attacking planets and ports."
										),
				"defendTrader" => array	(
										"Defend - Trader Attacks",
										"Defend your ally when they are attacked."
										),
				"napTrader" => array	(
										"Non Aggression - Traders",
										"Cease Fire against Traders."
										),
				"napPlanets" => array(
										"Non Aggression - Planets",
										"Cease Fire against Planets."
										),
				"napForces" => array(
										"Non Aggression - Forces",
										"Cease Fire against Forces.  Also allows refreshing of allied forces."
										),
				"aaAccess" => array(
										"Alliance Account Access",
										"Restrictions can be set in the roles section."
										),
				"mbRead" => array(
										"Message Board Read Rights",
										"Allow your ally to read your message board."
										),
				"mbWrite" => array(
										"Message Board Write Rights",
										"Allow your ally to post on your message board."
										),
				"modRead" => array(
										"Message of the Day Read Rights",
										"Allow your ally to read your message of the day."
										),
				"planetLand" => array(
										"Planet Landing Rights",
										"Allow your ally to land on your planets."
										)
				);
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
$db->query('SELECT leader_id, alliance_name, alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
$leader_id = $db->f("leader_id");
print_topic(stripslashes($db->f("alliance_name")) . ' (' . $db->f("alliance_id") . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($alliance_id,$db->f('leader_id'));
$db->query("SELECT * FROM alliance WHERE game_id = $player->game_id AND alliance_id != $player->alliance_id ORDER BY alliance_name");
while ($db->next_record()) $temp[$db->f("alliance_id")] = stripslashes($db->f("alliance_name"));
print("<div align=\"center\">");
if (isset($var['message'])) print($var['message'] . "<br />");
print("<br /><br />");
$db->query("SELECT * FROM alliance_treaties WHERE alliance_id_2 = $alliance_id AND game_id = $player->game_id AND official = 'FALSE'");
while ($db->next_record()) {
	print_topic("Treaty Offers");
	print("Treaty offer from <span class=\"yellow\">");
	print($temp[$db->f("alliance_id_1")]);
	print("</span>.  Terms as follows:<br /><ul>");
	if ($db->f("trader_assist")) print("<li>Assist - Trader Attacks</li>");
	if ($db->f("trader_defend")) print("<li>Defend - Trader Attacks</li>");
	if ($db->f("trader_nap")) print("<li>Non Aggression - Traders</li>");
	if ($db->f("raid_assist")) print("<li>Assist - Planet & Port Attacks</li>");
	if ($db->f("planet_nap")) print("<li>Non Aggression - Planets</li>");
	if ($db->f("forces_nap")) print("<li>Non Aggression - Forces</li>");
	if ($db->f("aa_access")) print("<li>Alliance Account Access</li>");
	if ($db->f("mb_read")) print("<li>Message Board Read Rights</li>");
	if ($db->f("mb_write")) print("<li>Message Board Write Rights</li>");
	if ($db->f("mod_read")) print("<li>Message of the Day Read Rights</li>");
	if ($db->f("planet_land")) print("<li>Planet Landing Rights</li>");
	print("</ul>");
	$container=create_container('alliance_treaties_processing.php','');
	$container['alliance_id'] = $alliance_id;
	$container['alliance_id_1'] = $db->f("alliance_id_1");
	$container['aa'] = $db->f("aa_access");
	$container['alliance_name'] = $temp[$db->f("alliance_id_1")];
	$container['accept'] = TRUE;
	print_button($container,'Accept');
	$container['accept'] = FALSE;
	print("&nbsp;");
	print_button($container,'Reject');
	print("<br /><br />");
}
print_topic("Offer A Treaty");
print("Select the alliance you wish to offer a treaty.<br /><small>Note: Treaties require 24 hours to be canceled once in effect</small><br>");
$container=array();
$container['url'] = 'skeleton.php';
$container['body'] = 'alliance_treaties_processing.php';
$container['alliance_id'] = $alliance_id;
$form = create_form($container,'Send the Offer');
echo $form['form'];
print("<select name=\"proposedAlliance\" id=\"InputFields\">");
foreach ($temp as $allId => $allName) print("<option value=\"$allId\">$allName</option>");
print("</select");
print("<br>Choose the treaty terms<br>");
print_table();
foreach ($types as $checkName => $displayInfo)
	print("<tr><td>" . $displayInfo[0] . "<br /><small>" . $displayInfo[1] . "</small></td><td><input type=\"checkbox\" name=\"" . $checkName . "\"></td></tr>");
print("<tr><td colspan=\"2\">");
print($form['submit']);
print("</td></tr></table></form></div>");
?>