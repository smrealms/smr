<?php

$under_attack_shields = ($ship->old_hardware[HARWDWARE_SHIELDS] != $ship->hardware[HARDWARE_SHIELDS]);
$under_attack_armor = ($ship->old_hardware[HARDWARE_ARMOR] != $ship->hardware[HARDWARE_ARMOR]);
$under_attack_drones = ($ship->old_hardware[HARDWARE_COMBAT] != $ship->hardware[HARDWARE_COMBAT]);

if ($under_attack_shields || $under_attack_armor || $under_attack_drones) {
	echo '
		<div id="attack_warning" class="attack_warning"><nobr>You are under attack!</nobr></div>
		<script type="text/javascript">
		SetBlink();
		</script>
		';
	$ship->mark_seen();
}

$db->query('SELECT message_type_id,COUNT(*) FROM player_has_unread_messages WHERE account_id=' . $player->account_id . ' AND game_id=' . $player->game_id . ' GROUP BY message_type_id');

if($db->nf()) {
	$messages = array();
	while($db->next_record()) {
		$messages[$db->f('message_type_id')] = $db->f('COUNT(*)');
	}

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'message_view.php';

	if(isset($messages[$GLOBALMSG])) {
		$container['folder_id'] = $GLOBALMSG;
		print_link($container, '<img src="images/global_msg.png" border="0" alt="Global Messages">');
		echo '<small>' . $messages[$GLOBALMSG] . '</small>';
	}

	if(isset($messages[$PLAYERMSG])) {
		$container['folder_id'] = $PLAYERMSG;
		print_link($container, '<img src="images/personal_msg.png" border="0" alt="Personal Messages">');
		echo '<small>' . $messages[$PLAYERMSG] . '</small>';
	}

	if(isset($messages[$SCOUTMSG])) {
		$container['folder_id'] = $SCOUTMSG;
		print_link($container, '<img src="images/scout_msg.png" border="0" alt="Scout Messages">');
		echo '<small>' . $messages[$SCOUTMSG] . '</small>';
	}

	if(isset($messages[$POLITICALMSG])) {
		$container['folder_id'] = $POLITICALMSG;
		print_link($container, '<img src="images/council_msg.png" border="0" alt="Political Messages">');
		echo '<small>' . $messages[$POLITICALMSG] . '</small>';
	}

	if(isset($messages[$ALLIANCEMSG])) {
		$container['folder_id'] = $ALLIANCEMSG;
		print_link($container, '<img src="images/alliance_msg.png" border="0" alt="Alliance Messages">');
		echo '<small>' . $messages[$ALLIANCEMSG] . '</small>';
	}

	if(isset($messages[$ADMINMSG])) {
		$container['folder_id'] = $ADMINMSG;
		print_link($container, '<img src="images/admin_msg.png" border="0" alt="Admin Messages">');
		echo '<small>' . $messages[$ADMINMSG] . '</small>';
	}

	if(isset($messages[$PLANETMSG])) {
		$container = array();
		$container['url'] = 'planet_msg_processing.php';
		print_link($container, '<img src="images/planet_msg.png" border="0" alt="Planet Messages">');
		echo '<small>' . $messages[$PLANETMSG] . '</small>';
	}
	echo '<br>';
}

echo $player->level_name . '<br><big>';

$container = array();
$container["url"]		= 'skeleton.php';
$container["body"]		= 'trader_search_result.php';
$container["player_id"]	= $player->player_id;
print_link($container, $player->get_colored_name());
echo '</big>';
if (in_array($player->account_id, $HIDDEN_PLAYERS)) print("<br /><span style=\"font-size:small\">INVISIBLE</span>");
echo '<br><br>Race : ' . $player->race_name;
echo '<br>Turns : ' . $player->turns . '<br>';

if ($player->newbie_turns > 0) {
	echo 'Newbie Turns Left: <span style="color:#';

	if ($player->newbie_turns > 20)
		echo '00BB00';
	else
		echo 'BB0000';

	echo ';">' . $player->newbie_turns  . '</span><br>';
}

echo 'Cash : ' . number_format($player->credits) . '<br>';
echo 'Experience : ' . number_format($player->experience) . '<br>';
echo 'Level : ' .  $player->level_id;
echo '<br>Alignment : ' . get_colored_text($player->alignment,$player->alignment);
echo '<br>Alliance : ' . $player->alliance_name;

if ($player->alliance_id > 0) echo ' (' . $player->alliance_id . ')';
echo '<br><br><b style="color:yellow;">' . $ship->ship_name . '</b><br>';

$db->query("SELECT ship_name FROM ship_has_name WHERE game_id = $player->game_id AND " .
			"account_id = $player->account_id LIMIT 1");
if ($db->next_record()) {
	//they have a name so we print it
	echo stripslashes($db->f("ship_name"));
}

echo 'Rating : ' . $ship->attack_rating() . '/' . $ship->defense_rating() . '<br>';

// ******* Shields *******
isset($ship->hardware[1]) ? $am=$ship->hardware[1] : $am=0;
echo 'Shields : ';
if ($under_attack_shields)
	echo '<span style="color:red;">' . $am . '</span>';
else
	echo $am;
echo '/' . $ship->max_hardware[HARDWARE_SHIELDS] . '<br>';

// ******* Armor *******
!empty($ship->hardware[2]) ? $am=$ship->hardware[2] : $am=0;
echo 'Armor : ';
if ($under_attack_armor)
	echo '<span style="color:red;">' . $am . '</span>';
else
	echo $am;
echo '/' . $ship->max_hardware[HARDWARE_ARMOR] . '<br>';

// ******* Hardware *******
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'configure_hardware.php';

print_link($container, '<strong>CIJSD</strong>');
echo ' : ';
!empty($ship->hardware[8]) ? $cijsd = '*' : $cijsd = '-';
!empty($ship->hardware[9]) ? $cijsd .= '*' : $cijsd .= '-';
!empty($ship->hardware[10]) ? $cijsd .= '*' : $cijsd .= '-';
!empty($ship->hardware[7]) ? $cijsd .= '*' : $cijsd .= '-';
!empty($ship->hardware[11]) ? $cijsd .= '*' : $cijsd .= '-';
echo $cijsd;
echo '<br /><br />';

if ($ship->cloak_active()) echo '<strong style="color:lime;">*** Cloak active ***</strong><br /><br />';
else if (!empty($ship->hardware[8])) echo '<strong style="color:red;">*** Cloak inactive ***</strong><br /><br />';


if ($ship->get_illusion() > 0) {

	$db->query('SELECT ship_name FROM ship_type WHERE ship_type_id = ' . $ship->get_illusion() . ' LIMIT 1');
	$db->next_record();
	$ship_name = $db->f('ship_name');
	echo '<strong style="color:cyan;"> ' . $ship_name . '</strong><br />IG Rating : (' . $ship->get_illusion_attack() . '/' . $ship->get_illusion_defense() . ')<br /><br />';

}

// ******* Forces *******
print_link(create_container('skeleton.php', 'forces_drop.php'), '<b>Forces</b>');
echo '<br>';

if (!empty($ship->hardware[HARDWARE_MINE])) {

	$container = array();
	$container['url'] = 'forces_drop_processing.php';
	$container['owner_id'] = $player->account_id;
	$container['drop_mines'] = 1;
	print_link($container, '<b>[x]</b> ');

}
echo 'Mines : ' . $ship->hardware[HARDWARE_MINE] . '/' . $ship->max_hardware[HARDWARE_MINE] . '<br>';

if (!empty($ship->hardware[HARDWARE_COMBAT])) {

	$container = array();
	$container['url'] = 'forces_drop_processing.php';
	$container['owner_id'] = $player->account_id;
	$container['drop_combat_drones'] = 1;
	print_link($container, '<b>[x]</b> ');

}
echo 'Combat : ' . $ship->hardware[HARDWARE_COMBAT] . '/' . $ship->max_hardware[HARDWARE_COMBAT] . '<br>';

if (!empty($ship->hardware[HARDWARE_SCOUT]))  {

	$container = array();
	$container['url'] = 'forces_drop_processing.php';
	$container['owner_id'] = $player->account_id;
	$container['drop_scout_drones'] = 1;
	print_link($container, '<b>[x]</b> ');

}
echo 'Scout : ' . $ship->hardware[HARDWARE_SCOUT] . '/' . $ship->max_hardware[HARDWARE_SCOUT];
echo '<br><br>';
print_link(create_container('skeleton.php', 'cargo_dump.php'), '<b>Cargo Holds</b>');

echo '&nbsp;(' . $ship->hardware[3] . '/' . $ship->max_hardware[3] . ')<br>';

foreach ($ship->cargo as $id => $amount)
	if ($amount > 0) {

		$db->query('SELECT good_name FROM good WHERE good_id=' .  $id);
		if ($db->next_record())
			echo '<img src="images/port/' . $id . '.png" alt="' . $db->f("good_name") . '">&nbsp;:&nbsp;' . $amount . '<br>';

	}

echo 'Empty : ' . $ship->cargo_left;
echo '<br><br>';
print_link(create_container('skeleton.php', 'weapon_reorder.php'), '<b>Weapons</b>');
echo '<br>';

foreach($ship->weapon as $weapon_name)
	echo $weapon_name . '<br>';

echo 'Open : ' . $ship->weapon_open;

?>
