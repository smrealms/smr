<?php

if ($player->hasNewbieTurns())
	create_error('You are under newbie protection!');
if ($player->hasFederalProtection())
	create_error('You are under federal protection!');
if($player->isLandedOnPlanet())
	create_error('You cannot attack ports whilst on a planet!');
if ($player->getTurns() < 3)
	create_error('You do not have enough turns to attack this port!');
if(!$ship->hasWeapons() && !$ship->hasCDs())
	create_error('What are you going to do? Insult it to death?');
if(!$player->canFight())
	create_error('You are not allowed to fight!');

$sector =& $player->getSector();
$port =& $sector->getPort();

if(!$port->exists())
	create_error('This port does not exist.');


if ($port->isDestroyed()) {
	$container=create_container('skeleton.php','port_attack.php');
	$container['sector_id'] = $port->getSectorID();
	forward($container);
}

// take the turns
$player->takeTurns(3,0);


// ********************************
// *
// * P o r t   a t t a c k
// *
// ********************************

$results = array('Attackers' => array('TotalDamage' => 0),
				'Forces' => array(),
				'Forced' => false);

$attackers =& $sector->getFightingTradersAgainstPort($player, $port);

$port->attackedBy($player,$attackers);

//decloak all attackers
foreach($attackers as &$attacker) {
	$attacker->getShip()->decloak();
} unset($attacker);

foreach($attackers as &$attacker) {
	$playerResults =& $attacker->shootPort($port);
	$results['Attackers']['Traders'][$attacker->getAccountID()]  =& $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($attacker);
$results['Attackers']['Downgrades'] = $port->checkForDowngrade($results['Attackers']['TotalDamage']);
$results['Port'] =& $port->shootPlayers($attackers);

$account->log(LOG_TYPE_PORT_RAIDING, 'Player attacks port, the port does '.$results['Port']['TotalDamage'].', their team does ' . $results['Attackers']['TotalDamage'] .' and downgrades '.$results['Attackers']['Downgrades'].' levels.', $port->getSectorID());

$ship->removeUnderAttack(); //Don't show attacker the under attack message.
$port->update();

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $db->escapeNumber($player->getGameID()) . ',\'PORT\',' . $db->escapeNumber($port->getSectorID()) . ',' . $db->escapeNumber(TIME) . ',' . $db->escapeNumber($player->getAccountID()) . ',' . $db->escapeNumber($player->getAllianceID()) . ','.$db->escapeNumber(ACCOUNT_ID_PORT).',' . $db->escapeNumber(PORT_ALLIANCE_ID) . ',' . $db->escapeBinary(gzcompress($serializedResults)) . ')');
unserialize($serializedResults); //because of references we have to undo this.
$logId = $db->escapeString('[ATTACK_RESULTS]'.$db->getInsertID());
foreach($attackers as &$attacker) {
	if(!$player->equals($attacker)) {
		$db->query('REPLACE INTO sector_message VALUES(' . $db->escapeNumber($attacker->getAccountID()) . ',' . $db->escapeNumber($attacker->getGameID()) . ','.$logId.')');
	}
} unset($attacker);

$container = create_container('skeleton.php','port_attack.php');
$container['sector_id'] = $port->getSectorID();

// If they died on the shot they get to see the results
if($player->isDead()) {
	$container['override_death'] = TRUE;
}

if ($port->isDestroyed()) {
    foreach($attackers as &$attacker) {
        $attacker->addOperationScore($port);
    }
}

$container['results'] = $serializedResults;
forward($container);


//function sendReport($results, $port) {
//	if (DEBUG) $PHP_OUTPUT.=('Sending Reports<br />');
//	global $player, $db;
//	$mainText = 'From the reports we have been able to gather the following information:<br /><br />';
//	$mainText .= $results[PORT_DISPLAY] . '<br />' . $results[PLAYER_DISPLAY];
//	if ($player->hasAlliance()) {
//		$topic = 'Port Siege Report Sector '.$player->getSectorID();
//		$text = 'Reports have come in from the space above <span class="yellow">Port ' . $player->getSectorID() . '</span> and have confirmed our <span class="red">siege</span>!<br />';
//		$text .= $mainText;
//		$text = mysql_real_escape_string($text);
//		$thread_id = 0;
//		$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND topic = '.$db->escapeString($topic).' LIMIT 1');
//		if ($db->nextRecord()) $thread_id = $db->getField('thread_id');
//		if ($thread_id == 0) {
//			$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' ORDER BY thread_id DESC LIMIT 1');
//			if ($db->nextRecord())
//				$thread_id = $db->getField('thread_id') + 1;
//			else $thread_id = 1;
//			$db->query('INSERT INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ' .
//						'(' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAllianceID()) . ', '.$thread_id.', '.$db->escapeString($topic).')');
//		}
//		$db->query('SELECT * FROM alliance_thread WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND ' .
//					'thread_id = '.$thread_id.' ORDER BY reply_id DESC LIMIT 1');
//		if ($db->nextRecord()) $reply_id = $db->getField('reply_id') + 1;
//		else $reply_id = 1;
//		$db->query('INSERT INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES ' .
//				'(' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAllianceID()) . ', '.$thread_id.', '.$reply_id.', '.$db->escapeString($text).', 0, ' . TIME . ')');
//	}
//}
?>