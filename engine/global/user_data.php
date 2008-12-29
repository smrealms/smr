<?

$under_attack_shields = ($ship->old_hardware[HARWDWARE_SHIELDS] != $ship->getShields());
$under_attack_armor = ($ship->old_hardware[HARDWARE_ARMOR] != $ship->getArmour());
$under_attack_drones = ($ship->old_hardware[HARDWARE_COMBAT] != $ship->getCDs());

if ($under_attack_shields || $under_attack_armor || $under_attack_drones) {
	echo '
		<div id="attack_warning" class="attack_warning"><nobr>You are under attack!</nobr></div>
		<script type="text/javascript">
		SetBlink();
		</script>
		';
	$ship->mark_seen();
}

$db->query('SELECT message_type_id,COUNT(*) FROM player_has_unread_messages WHERE account_id=' . $player->getAccountID() . ' AND game_id=' . $player->getGameID() . ' GROUP BY message_type_id');

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
		$PHP_OUTPUT.=create_link($container, '<img src="images/global_msg.gif" border="0" alt="Global Messages">');
		echo '<small>' . $messages[$GLOBALMSG] . '</small>';
	}

	if(isset($messages[$PLAYERMSG])) {
		$container['folder_id'] = $PLAYERMSG;
		$PHP_OUTPUT.=create_link($container, '<img src="images/personal_msg.gif" border="0" alt="Personal Messages">');
		echo '<small>' . $messages[$PLAYERMSG] . '</small>';
	}

	if(isset($messages[$SCOUTMSG])) {
		$container['folder_id'] = $SCOUTMSG;
		$PHP_OUTPUT.=create_link($container, '<img src="images/scout_msg.gif" border="0" alt="Scout Messages">');
		echo '<small>' . $messages[$SCOUTMSG] . '</small>';
	}

	if(isset($messages[$POLITICALMSG])) {
		$container['folder_id'] = $POLITICALMSG;
		$PHP_OUTPUT.=create_link($container, '<img src="images/council_msg.gif" border="0" alt="Political Messages">');
		echo '<small>' . $messages[$POLITICALMSG] . '</small>';
	}

	if(isset($messages[$ALLIANCEMSG])) {
		$container['folder_id'] = $ALLIANCEMSG;
		$PHP_OUTPUT.=create_link($container, '<img src="images/alliance_msg.gif" border="0" alt="Alliance Messages">');
		echo '<small>' . $messages[$ALLIANCEMSG] . '</small>';
	}

	if(isset($messages[$ADMINMSG])) {
		$container['folder_id'] = $ADMINMSG;
		$PHP_OUTPUT.=create_link($container, '<img src="images/admin_msg.gif" border="0" alt="Admin Messages">');
		echo '<small>' . $messages[$ADMINMSG] . '</small>';
	}

	if(isset($messages[$PLANETMSG])) {
		$container = array();
		$container['url'] = 'planet_msg_processing.php';
		$PHP_OUTPUT.=create_link($container, '<img src="images/planet_msg.gif" border="0" alt="Planet Messages">');
		echo '<small>' . $messages[$PLANETMSG] . '</small>';
	}
	echo '<br>';
}

echo $player->getLevelName() . '<br><big>';

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'trader_search_result.php';
$container['player_id']	= $player->getPlayerID();
$PHP_OUTPUT.=create_link($container, $player->getDisplayName());
echo '</big>';
if (in_array($player->getAccountID(), $HIDDEN_PLAYERS)) $PHP_OUTPUT.=('<br /><span style="font-size:small">INVISIBLE</span>');
echo '<br><br>Race : ' . $player->getRaceName();
echo '<br>Turns : ' . $player->getTurns() . '<br>';

if ($player->getNewbieTurns() > 0) {
	echo 'Newbie Turns Left: <span style="color:#';

	if ($player->getNewbieTurns() > 20)
		echo '00BB00';
	else
		echo 'BB0000';

	echo ';">' . $player->getNewbieTurns()  . '</span><br>';
}

echo 'Cash : ' . number_format($player->getCredits()) . '<br>';
echo 'Experience : ' . number_format($player->getExperience()) . '<br>';
echo 'Level : ' .  $player->getLevelID();
echo '<br>Alignment : ' . get_colored_text($player->getAlignment(),$player->getAlignment());
echo '<br>Alliance : ' . $player->getAllianceName();

if ($player->getAllianceID() > 0) echo ' (' . $player->getAllianceID() . ')';
echo '<br><br><b style="color:yellow;">' . $ship->getName() . '</b><br>';

$db->query('SELECT ship_name FROM ship_has_name WHERE game_id = '.$player->getGameID().' AND ' .
			'account_id = '.$player->getAccountID().' LIMIT 1');
if ($db->next_record()) {
	//they have a name so we echo it
	echo stripslashes($db->f('ship_name'));
}

echo 'Rating : ' . $ship->getAttackRating() . '/' . $ship->getDefenseRating() . '<br>';

// ******* Shields *******
$am=$ship->getShields();
echo 'Shields : ';
if ($under_attack_shields)
	echo '<span style="color:red;">' . $am . '</span>';
else
	echo $am;
echo '/' . $ship->getMaxShields() . '<br>';

// ******* Armor *******
$am=$ship->getArmour();
echo 'Armor : ';
if ($under_attack_armor)
	echo '<span style="color:red;">' . $am . '</span>';
else
	echo $am;
echo '/' . $ship->getMaxArmour() . '<br>';

//var_dump($ship);
// ******* Hardware *******
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'configure_hardware.php';

$PHP_OUTPUT.=create_link($container, '<strong>CIJSD</strong>');
echo ' : ';
($ship->hasCloak()) ? $cijsd = '*' : $cijsd = '-';
($ship->hasIllusion()) ? $cijsd .= '*' : $cijsd .= '-';
($ship->hasJump()) ? $cijsd .= '*' : $cijsd .= '-';
$ship->hasScanner() ? $cijsd .= '*' : $cijsd .= '-';
($ship->hasDCS()) ? $cijsd .= '*' : $cijsd .= '-';
echo $cijsd;
echo '<br /><br />';

if ($ship->cloak_active()) echo '<strong style="color:lime;">*** Cloak active ***</strong><br /><br />';
else if (!empty($ship->hardware[8])) echo '<strong style="color:red;">*** Cloak inactive ***</strong><br /><br />';


if ($ship->hasActiveIllusion())
{
	echo '<strong style="color:cyan;"> ' . $ship->getIllusionShipName() . '</strong><br/>IG Rating : (' . $ship->getIllusionAttack() . '/' . $ship->getIllusionDefense() . ')<br /><br />';

}

// ******* Forces *******
$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'forces_drop.php'), '<b>Forces</b>');
echo '<br>';

if ($ship->hasMines()) {

	$container = array();
	$container['url'] = 'forces_drop_processing.php';
	$container['owner_id'] = $player->getAccountID();
	$container['drop_mines'] = 1;
	$PHP_OUTPUT.=create_link($container, '<b>[x]</b> ');

}
echo 'Mines : ' . $ship->getMines() . '/' . $ship->getMaxMines() . '<br>';

if ($ship->hasCDs()) {

	$container = array();
	$container['url'] = 'forces_drop_processing.php';
	$container['owner_id'] = $player->getAccountID();
	$container['drop_combat_drones'] = 1;
	$PHP_OUTPUT.=create_link($container, '<b>[x]</b> ');

}
echo 'Combat : ' . $ship->getCDs() . '/' . $ship->getMaxCDs() . '<br>';

if ($ship->hasSDs())  {

	$container = array();
	$container['url'] = 'forces_drop_processing.php';
	$container['owner_id'] = $player->getAccountID();
	$container['drop_scout_drones'] = 1;
	$PHP_OUTPUT.=create_link($container, '<b>[x]</b> ');

}
echo 'Scout : ' . $ship->getSDs() . '/' . $ship->getMaxSDs();
echo '<br><br>';
$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'cargo_dump.php'), '<b>Cargo Holds</b>');

echo '&nbsp;(' . $ship->getCargoHolds() . '/' . $ship->getMaxCargoHolds() . ')<br>';
$cargo = $ship->getCargo();
foreach ($cargo as $id => $amount)
	if ($amount > 0) {

		$db->query('SELECT good_name FROM good WHERE good_id=' .  $id);
		if ($db->next_record())
			echo '<img src="images/port/' . $id . '.gif" alt="' . $db->f('good_name') . '">&nbsp;:&nbsp;' . $amount . '<br>';

	}

echo 'Empty : ' . $ship->getEmptyHolds();
echo '<br><br>';
$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'weapon_reorder.php'), '<b>Weapons</b>');
echo '<br>';

foreach($ship->weapon as $weapon_name)
	echo $weapon_name . '<br>';

echo 'Open : ' . $ship->weapon_open;

?>
