<?

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

require_once(get_file_loc('SmrPort.class.inc'));
$port =& SmrPort::getPort($player->getGameID(), $player->getSectorID());

if(!$port->exists())
	create_error('This port does not exist.');
	
	
if ($port->isDestroyed())
{
	$container=create_container('skeleton.php','port_attack_new.php');
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

require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);
$attackers =& $sector->getFightingTradersAgainstPort($player, $port);

$port->attackedBy($player,$attackers);

//decloak all attackers
foreach($attackers as &$attacker)
{
	$attacker->getShip()->decloak();
} unset($attacker);

foreach($attackers as &$attacker)
{
	$playerResults =& $attacker->shootPort($port);
	$results['Attackers']['Traders'][$attacker->getAccountID()]  =& $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($attacker);
$account->log(7, 'Player attacks port their team does ' . $results['Attackers']['TotalDamage'], $port->getSectorID());
$results['Attackers']['Downgrades'] = $port->checkForDowngrade($results['Attackers']['TotalDamage']);

$results['Port'] =& $port->shootPlayers($attackers);

$ship->removeUnderAttack(); //Don't show attacker the under attack message.
$port->update();

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $player->getGameID() . ',\'PORT\',' . $player->getSectorID() . ',' . TIME . ',' . $player->getAccountID() . ',' . $player->getAllianceID() . ','.PORT_ACCOUNT_ID.',' . PORT_ALLIANCE_ID . ',' . $db->escape_string(gzcompress($serializedResults)) . ', \'FALSE\')');
unserialize($serializedResults); //because of references we have to undo this.

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'port_attack_new.php';
$container['sector_id'] = $port->getSectorID();

// If they died on the shot they get to see the results
if($player->isDead())
{
	$container['override_death'] = TRUE;
}

$container['results'] = $serializedResults;
forward($container);


//function sendReport($results, $port) {
//	if (DEBUG) $PHP_OUTPUT.=('Sending Reports<br />');
//	global $player, $db;
//	$mainText = 'From the reports we have been able to gather the following information:<br /><br />';
//	$mainText .= $results[PORT_DISPLAY] . '<br />' . $results[PLAYER_DISPLAY];
//	if ($player->getAllianceID() > 0) {
//		$topic = 'Port Siege Report Sector '.$player->getSectorID();
//		$text = 'Reports have come in from the space above <span class="yellow">Port ' . $player->getSectorID() . '</span> and have confirmed our <span class="red">siege</span>!<br />';
//		$text .= $mainText;
//		$text = mysql_real_escape_string($text);
//		$thread_id = 0;
//		$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$player->getAllianceID().' AND topic = '.$db->escapeString($topic).' LIMIT 1');
//		if ($db->nextRecord()) $thread_id = $db->getField('thread_id');
//		if ($thread_id == 0)
//		{
//			$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$player->getAllianceID().' ORDER BY thread_id DESC LIMIT 1');
//			if ($db->nextRecord())
//				$thread_id = $db->getField('thread_id') + 1;
//			else $thread_id = 1;
//			$db->query('INSERT INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ' .
//						'('.$player->getGameID().', '.$player->getAllianceID().', '.$thread_id.', '.$db->escapeString($topic).')');
//		}
//		$db->query('SELECT * FROM alliance_thread WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND ' .
//					'thread_id = '.$thread_id.' ORDER BY reply_id DESC LIMIT 1');
//		if ($db->nextRecord()) $reply_id = $db->getField('reply_id') + 1;
//		else $reply_id = 1;
//		$db->query('INSERT INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES ' .
//				'('.$player->getGameID().', '.$player->getAllianceID().', '.$thread_id.', '.$reply_id.', '.$db->escapeString($text).', 0, ' . TIME . ')');
//	}
//}
?>