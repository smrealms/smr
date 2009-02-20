<?

$under_attack_shields = ($ship->old_hardware[HARWDWARE_SHIELDS] != $ship->getShields());
$under_attack_armour = ($ship->old_hardware[HARDWARE_ARMOUR] != $ship->getArmour());
$under_attack_drones = ($ship->old_hardware[HARDWARE_COMBAT] != $ship->getCDs());

if ($under_attack_shields || $under_attack_armour || $under_attack_drones) {
	$PHP_OUTPUT.= '
		<div id="attack_warning" class="attack_warning"><nobr>You are under attack!</nobr></div>
		<script type="text/javascript">
		SetBlink();
		</script>
		';
	$ship->removeUnderAttack();
}

$db->query('SELECT message_type_id,COUNT(*) FROM player_has_unread_messages WHERE account_id=' . $player->getAccountID() . ' AND game_id=' . $player->getGameID() . ' GROUP BY message_type_id');

if($db->getNumRows()) {
	$messages = array();
	while($db->nextRecord()) {
		$messages[$db->getField('message_type_id')] = $db->getField('COUNT(*)');
	}

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'message_view.php';

	if(isset($messages[MSG_GLOBAL])) {
		$container['folder_id'] = MSG_GLOBAL;
		$PHP_OUTPUT.=create_link($container, '<img src="images/global_msg.gif" border="0" alt="Global Messages">');
		$PHP_OUTPUT.= '<small>' . $messages[MSG_GLOBAL] . '</small>';
	}

	if(isset($messages[MSG_PLAYER])) {
		$container['folder_id'] = MSG_PLAYER;
		$PHP_OUTPUT.=create_link($container, '<img src="images/personal_msg.gif" border="0" alt="Personal Messages">');
		$PHP_OUTPUT.= '<small>' . $messages[MSG_PLAYER] . '</small>';
	}

	if(isset($messages[MSG_SCOUT])) {
		$container['folder_id'] = MSG_SCOUT;
		$PHP_OUTPUT.=create_link($container, '<img src="images/scout_msg.gif" border="0" alt="Scout Messages">');
		$PHP_OUTPUT.= '<small>' . $messages[MSG_SCOUT] . '</small>';
	}

	if(isset($messages[MSG_POLITICAL])) {
		$container['folder_id'] = MSG_POLITICAL;
		$PHP_OUTPUT.=create_link($container, '<img src="images/council_msg.gif" border="0" alt="Political Messages">');
		$PHP_OUTPUT.= '<small>' . $messages[MSG_POLITICAL] . '</small>';
	}

	if(isset($messages[MSG_ALLIANCE])) {
		$container['folder_id'] = MSG_ALLIANCE;
		$PHP_OUTPUT.=create_link($container, '<img src="images/alliance_msg.gif" border="0" alt="Alliance Messages">');
		$PHP_OUTPUT.= '<small>' . $messages[MSG_ALLIANCE] . '</small>';
	}

	if(isset($messages[MSG_ADMIN])) {
		$container['folder_id'] = MSG_ADMIN;
		$PHP_OUTPUT.=create_link($container, '<img src="images/admin_msg.gif" border="0" alt="Admin Messages">');
		$PHP_OUTPUT.= '<small>' . $messages[MSG_ADMIN] . '</small>';
	}

	if(isset($messages[MSG_PLANET])) {
		$container = array();
		$container['url'] = 'planet_msg_processing.php';
		$PHP_OUTPUT.=create_link($container, '<img src="images/planet_msg.gif" border="0" alt="Planet Messages">');
		$PHP_OUTPUT.= '<small>' . $messages[MSG_PLANET] . '</small>';
	}
	$PHP_OUTPUT.= '<br />';
}

$PHP_OUTPUT.= $player->getLevelName() . '<br /><big>';

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'trader_search_result.php';
$container['player_id']	= $player->getPlayerID();
$PHP_OUTPUT.=create_link($container, $player->getDisplayName());
$PHP_OUTPUT.= '</big>';
if (in_array($player->getAccountID(), $HIDDEN_PLAYERS)) $PHP_OUTPUT.=('<br /><span style="font-size:small">INVISIBLE</span>');
$PHP_OUTPUT.= '<br /><br />Race : ' . $player->getRaceName();
$PHP_OUTPUT.= '<br />Turns : ' . $player->getTurns() . '<br />';

if ($player->getNewbieTurns() > 0) {
	$PHP_OUTPUT.= 'Newbie Turns Left: <span style="color:#';

	if ($player->getNewbieTurns() > 20)
		$PHP_OUTPUT.= '00BB00';
	else
		$PHP_OUTPUT.= 'BB0000';

	$PHP_OUTPUT.= ';">' . $player->getNewbieTurns()  . '</span><br />';
}

$PHP_OUTPUT.= 'Cash : ' . number_format($player->getCredits()) . '<br />';
$PHP_OUTPUT.= 'Experience : ' . number_format($player->getExperience()) . '<br />';
$PHP_OUTPUT.= 'Level : ' .  $player->getLevelID();
$PHP_OUTPUT.= '<br />Alignment : ' . get_colored_text($player->getAlignment(),$player->getAlignment());
$PHP_OUTPUT.= '<br />Alliance : ' . $player->getAllianceName();

if ($player->getAllianceID() > 0) $PHP_OUTPUT.= ' (' . $player->getAllianceID() . ')';
$PHP_OUTPUT.= '<br /><br /><b style="color:yellow;">' . $ship->getName() . '</b><br />';

$db->query('SELECT ship_name FROM ship_has_name WHERE game_id = '.$player->getGameID().' AND ' .
			'account_id = '.$player->getAccountID().' LIMIT 1');
if ($db->nextRecord()) {
	//they have a name so we $PHP_OUTPUT.= it
	$PHP_OUTPUT.= stripslashes($db->getField('ship_name'));
}

$PHP_OUTPUT.= 'Rating : ' . $ship->getAttackRating() . '/' . $ship->getDefenseRating() . '<br />';

// ******* Shields *******
$am=$ship->getShields();
$PHP_OUTPUT.= 'Shields : ';
if ($under_attack_shields)
	$PHP_OUTPUT.= '<span style="color:red;">' . $am . '</span>';
else
	$PHP_OUTPUT.= $am;
$PHP_OUTPUT.= '/' . $ship->getMaxShields() . '<br />';

// ******* Armour *******
$am=$ship->getArmour();
$PHP_OUTPUT.= 'Armour : ';
if ($under_attack_armour)
	$PHP_OUTPUT.= '<span style="color:red;">' . $am . '</span>';
else
	$PHP_OUTPUT.= $am;
$PHP_OUTPUT.= '/' . $ship->getMaxArmour() . '<br />';

//var_dump($ship);
// ******* Hardware *******
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'configure_hardware.php';

$PHP_OUTPUT.=create_link($container, '<strong>CIJSD</strong>');
$PHP_OUTPUT.= ' : ';
($ship->hasCloak()) ? $cijsd = '*' : $cijsd = '-';
($ship->hasIllusion()) ? $cijsd .= '*' : $cijsd .= '-';
($ship->hasJump()) ? $cijsd .= '*' : $cijsd .= '-';
$ship->hasScanner() ? $cijsd .= '*' : $cijsd .= '-';
($ship->hasDCS()) ? $cijsd .= '*' : $cijsd .= '-';
$PHP_OUTPUT.= $cijsd;
$PHP_OUTPUT.= '<br /><br />';

if ($ship->isCloaked()) $PHP_OUTPUT.= '<strong style="color:lime;">*** Cloak active ***</strong><br /><br />';
else if (!empty($ship->hardware[8])) $PHP_OUTPUT.= '<strong style="color:red;">*** Cloak inactive ***</strong><br /><br />';


if ($ship->hasActiveIllusion())
{
	$PHP_OUTPUT.= '<strong style="color:cyan;"> ' . $ship->getIllusionShipName() . '</strong><br />IG Rating : (' . $ship->getIllusionAttack() . '/' . $ship->getIllusionDefense() . ')<br /><br />';

}

// ******* Forces *******
$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'forces_drop.php'), '<b>Forces</b>');
$PHP_OUTPUT.= '<br />';

if ($ship->hasMines()) {

	$container = array();
	$container['url'] = 'forces_drop_processing.php';
	$container['owner_id'] = $player->getAccountID();
	$container['drop_mines'] = 1;
	$PHP_OUTPUT.=create_link($container, '<b>[x]</b> ');

}
$PHP_OUTPUT.= 'Mines : ' . $ship->getMines() . '/' . $ship->getMaxMines() . '<br />';

if ($ship->hasCDs()) {

	$container = array();
	$container['url'] = 'forces_drop_processing.php';
	$container['owner_id'] = $player->getAccountID();
	$container['drop_combat_drones'] = 1;
	$PHP_OUTPUT.=create_link($container, '<b>[x]</b> ');

}
$PHP_OUTPUT.= 'Combat : ' . $ship->getCDs() . '/' . $ship->getMaxCDs() . '<br />';

if ($ship->hasSDs())  {

	$container = array();
	$container['url'] = 'forces_drop_processing.php';
	$container['owner_id'] = $player->getAccountID();
	$container['drop_scout_drones'] = 1;
	$PHP_OUTPUT.=create_link($container, '<b>[x]</b> ');

}
$PHP_OUTPUT.= 'Scout : ' . $ship->getSDs() . '/' . $ship->getMaxSDs();
$PHP_OUTPUT.= '<br /><br />';
$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'cargo_dump.php'), '<b>Cargo Holds</b>');

$PHP_OUTPUT.= '&nbsp;(' . $ship->getCargoHolds() . '/' . $ship->getMaxCargoHolds() . ')<br />';
$cargo = $ship->getCargo();
foreach ($cargo as $id => $amount)
	if ($amount > 0) {

		$db->query('SELECT good_name FROM good WHERE good_id=' .  $id);
		if ($db->nextRecord())
			$PHP_OUTPUT.= '<img src="images/port/' . $id . '.gif" alt="' . $db->getField('good_name') . '">&nbsp;:&nbsp;' . $amount . '<br />';

	}

$PHP_OUTPUT.= 'Empty : ' . $ship->getEmptyHolds();
$PHP_OUTPUT.= '<br /><br />';
$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'weapon_reorder.php'), '<b>Weapons</b>');
$PHP_OUTPUT.= '<br />';

foreach($ship->weapon as $weapon_name)
	$PHP_OUTPUT.= $weapon_name . '<br />';

$PHP_OUTPUT.= 'Open : ' . $ship->weapon_open;

?>
