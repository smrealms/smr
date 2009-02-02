<?

if ($player->hasNewbieTurns())
	create_error('You are under newbie protection!');
if ($player->hasFederalProtection())
	create_error('You are under federal protection!');
if($player->isLandedOnPlanet())
	create_error('You cannot attack ports whilst on a planet!');
if ($player->getTurns() < 3)
	create_error('You do not have enough turns to attack this port!');
if(!$player->canFight())
	create_error('You are not allowed to fight!');

require_once(get_file_loc('SmrPort.class.inc'));
$port =& SmrPort::getPort($player->getGameID(), $player->getSectorID());

if(!$port->exists())
	create_error('This port does not exist.');

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

$results['Port'] =& $port->shootPlayers($attackers);

$ship->removeUnderAttack(); //Don't show attacker the under attack message.

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
//
//
//
////general info
//define ('MAX_TURNS', (Globals::getGameSpeed($player->getGameID()) * 400));
//$limit = 10;
//define ('MAXIMUM_FLEET_SIZE',$limit);
//
////result info
//define('SHIELD_DMG_DONE', 0);
//define('DRONE_DMG_DONE', 1);
//define('ARMOR_DMG_DONE', 2);
//define('DRONES_FIRED', 3);
//define('RESULT_OF_WEAPON', 4);
//define('TARGET',5);
//define('DRONES_HIT_BEHIND_SHIELDS',6);
//
////result[4]
//define('NORMAL_HIT', 0);
//define('SHIELD_ON_DRONES',1);
//define('ARMOR_ON_SHIELD',2);
//define('PORT_DEAD',3);
//define('FINAL_HIT',4);
//define('WEAPON_MISS',5);
//define('ALREADY_DEAD',6);
//define('SHIELD_ON_ARMOR',7);
//
////player array info
//define('PLAYER_ID', 0);
//define('PLAYER_NAME', 1);
//define('ALLIANCE_ID',2);
//define('RACE_ID', 3);
//define('CREDITS', 4);
//define('TURNS', 5);
//define('ALIGNMENT', 6);
//define('SHIP_ID', 7);
//define('EXPERIENCE', 8);
//define('LEVEL', 9);
//define('SHIELDS', 10);
//define('ARMOR', 11);
//define('DRONES', 12);
//define('DRONES_ORIGINAL', 13);
//define('DCS', 14);
//define('WEAPONS', 15);
//define('RESULTS', 16);
//define('EXPERIENCE_GAINED', 17);
//define('KILLER', 18); 
//define('TOTAL_DAMAGE',19);
//define('PERSONAL_DISPLAY', 20);
//
////weapon array info
//define('WEAPON_NAME', 0);
//define('SHIELD_DAMAGE', 1);
//define('ARMOR_DAMAGE', 2);
//define('ACCURACY', 3);
//
////port array info
//define('PORT_SHIELDS', 0);
//define('PORT_DRONES', 1);
//define('PORT_ARMOR', 2);
//define('STARTED', 3);
//define('PORT_LEVEL',4);
//define('PORT_RESULTS',5);
//define('REFRESH',6);
//define('PORT_RACE_ID',7);
//define('FED_ARRIVAL',8);
//define('PORT_CREDITS',9);
//define('NUM_TURRETS',15);
//
////more general
//define('MESSAGE_EXPIRES', TIME + 259200);
//
////final results info
//define('PORT_DISPLAY',0);
//define('PLAYER_DISPLAY',1);
//define('TOTAL_PLAYER_DMG',2);
//define('TOTAL_PORT_DMG',3);
//define('DMG_TO_PLAYER',4);
//define('DEBUG',0);
//function getPortArray()
//{
//	if (DEBUG) $PHP_OUTPUT.=('Entered port array<br />');
//	global $db, $player;
//	$db->query('SELECT  * FROM port WHERE sector_id = ' . $player->getSectorID() . ' AND game_id = ' . $player->getGameID());
//	if ($db->nextRecord()) {
//		$started = $db->getField('attack_started');
//		$refresh = $db->getField('reinforce_time');
//		$level = $db->getField('level');
//		if ($refresh < TIME) {
//			//add news message
//			processNews();
//			//defences restock (check for fed arrival)
//			$minsToStay = 30;
//			if ($refresh + $minsToStay * 60 > TIME)
//				$federal_mod = (TIME - $refresh - $minsToStay * 60) / (-6 * $minsToStay);
//			else $federal_mod = 0;
//			if ($federal_mod < 0) $federal_mod = 0;
//			$rich_mod = floor( $db->getField('credits') * 1e-7 );
//			if($rich_mod < 0) $rich_mod = 0;
//			$shields = round(($level * 1000 + 1000) + ($rich_mod * 500) + ($federal_mod * 500));
//			$armor = round(($level * 1000 + 1000) + ($rich_mod * 500) + ($federal_mod * 500));
//			$drones = round(($level * 100 + 100) + ($rich_mod * 50) + ($federal_mod * 50));
//			$refresh = TIME + ($level * 5 * 60);
//			$started = TIME;
//		} else {
//			$shields = $db->getField('shields');
//			$armor = $db->getField('armor');
//			$drones = $db->getField('combat_drones');
//		}
//		$port = array($shields,$drones,$armor,$started,$level,array(),$refresh,$db->getField('race_id'));
//		$port[PORT_CREDITS] = $db->getField('credits');
//	} else {
//		create_error('Port does not exist');
//	}
//	if ($port[PORT_ARMOR] == 0 && $port[PORT_SHIELDS] == 0) {
//		$container=create_container('skeleton.php','port_attack_new.php');
//		$container['continue'] = FALSE;
//		forward($container);
//	}
//	return $port;
//}
//function processNews() {
//	global $db, $player;
//	if (DEBUG) $PHP_OUTPUT.=('News Process<br />');
//	$text = '<span class="red bold">*MAYDAY* *MAYDAY*</span> The federal government has received a distress call from the port in sector';
//	$text .= ' <span class="yellow">'.$player->getSectorID().'</span>.';
//	if ($player->getAllianceID()) {
//		$text .= '  It is under attack by members of <span class="yellow">'.$player->getAllianceName().'</span>. ';
//		$text .= ' The Federal Government is offering various bounties for the deaths of any raiding members of <span class="yellow">'.$player->getAllianceName().'</span>';
//	} else {
//		$text .= '  It is under attack by <span class="yellow">'.$player->getPlayerName().'</span>';
//		$text .= ' The Federal Government is offering a bounty of ' . round($player->getLevelID() * .4) . ' million credits for the death of <span class="yellow">'.$player->getPlayerName().'</span>';
//	}
//	$text .= ' prior to the destruction of the port, or until federal forces arrive to defend the port.';
//	$text = mysql_real_escape_string($text);
//	$db->query('INSERT INTO news (game_id, time, news_message, type) VALUES ('.$player->getGameID().', ' . TIME . ', '.$db->escapeString($text).', \'regular\')');
//}
//function hofTracker($players, $port) {
//	if (DEBUG) $PHP_OUTPUT.=('Tracking HoF<br />');
//	global $db, $player;
//	//keep track of players who should get credit if the PB is successful.
//	$allowed = TIME - 60 * 60 * 3;
//	$db->query('DELETE FROM player_attacks_port WHERE time < '.$allowed);
//	foreach ($players as $accId => $crap) {
//		$db->query('REPLACE INTO player_attacks_port (game_id, account_id, sector_id, time, level) VALUES ' .
//					'('.$player->getGameID().', '.$accId.', '.$player->getSectorID().', ' . TIME . ', ' . $port[PORT_LEVEL] . ')');
//	}
//}
//function processResults(&$players, &$port, $fleet, $weapons) {
//	if (DEBUG) $PHP_OUTPUT.=('Processing Results<br />');
//	global $db, $player;
//	$results = array('','',0,0,array());
//	//port is updated in downgrade function, all we need to do is format text
//	$portDisplay = '<h2>Port Results</h2>';
//	$totalPortDamage = 0;
//	foreach ($port[PORT_RESULTS] as $resultArray) {
//		$totalPortDamage += $resultArray[SHIELD_DMG_DONE] + $resultArray[DRONE_DMG_DONE] + $resultArray[ARMOR_DMG_DONE];
//		$portDisplay .= '<span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '</span>';
//		if ($resultArray[DRONES_FIRED]) $portDisplay .= ' launches ' . $resultArray[DRONES_FIRED] . ' drones at ';
//		else $portDisplay .= ' fires a turret at ';
//		if ($resultArray[RESULT_OF_WEAPON] == ALREADY_DEAD) $portDisplay .= 'the debris that used to be ';
//		$portDisplay .= $players[$resultArray[TARGET]][PLAYER_NAME];
//		if ($resultArray[RESULT_OF_WEAPON] == NORMAL_HIT || $resultArray[RESULT_OF_WEAPON] == FINAL_HIT) $portDisplay .= ' destroying ';
//		elseif ($resultArray[RESULT_OF_WEAPON] == WEAPON_MISS) $portDisplay .= ' and misses';
//		elseif ($resultArray[RESULT_OF_WEAPON] == ALREADY_DEAD) $portDisplay .= '';
//		else $portDisplay .= ' and I have no idea what the hell happens!  Please save this screen and notify Azool.';
//		if ($resultArray[SHIELD_DMG_DONE]) $portDisplay .= '<span class="cyan">' . $resultArray[SHIELD_DMG_DONE] . '</span> shields';
//		if ($resultArray[DRONE_DMG_DONE]) {
//			if ($resultArray[SHIELD_DMG_DONE])
//				if ($resultArray[ARMOR_DMG_DONE]) $portDisplay .= ', ';
//				else $portDisplay .= ' and ';
//			$portDisplay .= '<span class="yellow">';
//			$portDisplay .= floor($resultArray[DRONE_DMG_DONE] / 3);
//			$portDisplay .= '</span> drones';
//		} if ($resultArray[ARMOR_DMG_DONE]) {
//			if ($resultArray[DRONE_DMG_DONE] || $resultArray[SHIELD_DMG_DONE]) $portDisplay .= ' and ';
//			$portDisplay .= '<span class="red">' . $resultArray[ARMOR_DMG_DONE] . '</span> armor';
//		}
//		$portDisplay .= '.<br />';
//		if ($resultArray[RESULT_OF_WEAPON] == FINAL_HIT) {
//			$portDisplay .= '<span style="color:yellow;">' . $players[$resultArray[TARGET]][PLAYER_NAME] . '</span>';
//			$portDisplay .= ' is <span style="color:red;">DESTROYED.</span><br />';
//		}
//		$results[DMG_TO_PLAYER][$resultArray[TARGET]] += $resultArray[SHIELD_DMG_DONE] + $resultArray[DRONE_DMG_DONE] + $resultArray[ARMOR_DMG_DONE];
//	}
//	$portDisplay .= '<br /><span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '</span>';
//	$portDisplay .= ' does a total of <span class="red">'.$totalPortDamage.'</span> damage.<br />';
//	$results[PORT_DISPLAY] = $portDisplay;
//	$results[TOTAL_PORT_DMG] = $totalPortDamage;
//	$playerDisplay = '<h2>Attacker Results</h2>';
//	$totalPlayerDamage = 0;
//	foreach ($fleet as $accId) {
//		$traderDisplay = '';
//		$weapon = 0;
//		$totalTraderDamage = 0;
//		//make sure this element exists to prevent blank messages
//		$results[DMG_TO_PLAYER][$accId] += 0;
//		foreach ($players[$accId][RESULTS] as $resultArray) {
//			$totalTraderDamage += $resultArray[SHIELD_DMG_DONE] + $resultArray[DRONE_DMG_DONE] + $resultArray[ARMOR_DMG_DONE] + (floor($resultArray[DRONES_HIT_BEHIND_SHIELDS] / 60) * 60);
//			$traderDisplay .= $players[$accId][PLAYER_NAME];
//			if(!$players[$accId][WEAPONS][$weapon])
//				if($resultArray[DRONES_FIRED]) $traderDisplay .= ' launches <span class="yellow">' . $resultArray[DRONES_FIRED] . '</span> drones';
//				else $traderDisplay .= ' fails to launch their drones';
//			else {
//				$traderDisplay .= ' fires their ';
//				$traderDisplay .= $weapons[$players[$accId][WEAPONS][$weapon]][WEAPON_NAME];
//			}
//			$traderDisplay .= ' at ';
//			if($resultArray[RESULT_OF_WEAPON] == PORT_DEAD)
//				$traderDisplay .= ' the remnants of ';
//			$traderDisplay .= '<span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '</span>';
//			if($resultArray[RESULT_OF_WEAPON] == ARMOR_ON_SHIELD)
//				$traderDisplay .= ' which is deflected by its shields.';
//			else if ($resultArray[RESULT_OF_WEAPON] == SHIELD_ON_ARMOR)
//				$traderDisplay .= ' which proves ineffective against its armor.';
//			else if ($resultArray[RESULT_OF_WEAPON] == SHIELD_ON_DRONES)
//				$traderDisplay .= ' which proves ineffective against its combat drones.';
//			else if ($resultArray[RESULT_OF_WEAPON] == WEAPON_MISS && $players[$accId][WEAPONS][$weapon])
//				$traderDisplay .= ' and misses every critical system.';
//			else if($resultArray[RESULT_OF_WEAPON] == PORT_DEAD)
//				$traderDisplay .= '.';
//			else {
//				$traderDisplay .= ' destroying ';
//				if($resultArray[SHIELD_DMG_DONE])
//					$traderDisplay .= '<span class="cyan">' . $resultArray[SHIELD_DMG_DONE] . '</span> shields';
//				if($resultArray[DRONE_DMG_DONE] || $resultArray[DRONES_HIT_BEHIND_SHIELDS]) {
//					if($resultArray[SHIELD_DMG_DONE] && $resultArray[ARMOR_DMG_DONE])
//						$traderDisplay .= ', ';
//					elseif ($resultArray[SHIELD_DMG_DONE])
//						$traderDisplay .= ' and ';
//					$traderDisplay .= '<span class="yellow">';
//					if ($resultArray[DRONE_DMG_DONE]) $traderDisplay .= floor($resultArray[DRONE_DMG_DONE] / 3);
//					else $traderDisplay .= floor($resultArray[DRONES_HIT_BEHIND_SHIELDS] / 60);
//					$traderDisplay .= '</span> drones';
//				}
//				if ($resultArray[ARMOR_DMG_DONE]) {
//					if ($resultArray[SHIELD_DMG_DONE] || $resultArray[DRONE_DMG_DONE] || $resultArray[DRONES_HIT_BEHIND_SHIELDS])
//						$traderDisplay .= ' and ';
//					$traderDisplay .= '<span class="red">' . $resultArray[ARMOR_DMG_DONE] . '</span> armor';
//				}
//				$traderDisplay .= '.';
//			}
//			$traderDisplay .= '<br />';
//			if ($resultArray[RESULT_OF_WEAPON] == FINAL_HIT) {
//				$traderDisplay .= '<span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '\'s</span> defenses are ';
//				$traderDisplay .= '<span style="color:red;">DESTROYED.</span><br />';
//				$traderDisplay .= $players[$accId][PLAYER_NAME] . ' claims <span class="yellow">' . number_format($port[PORT_CREDITS]) . '</span> credits from the port.<br />';
//				$players[$accId][CREDITS] += $port[PORT_CREDITS];
//				$port[PORT_CREDITS] = 0;
//				//get all players involved for HoF
//				$allowed = TIME - 60 * 60 * 3;
//				$db->query('SELECT * FROM player_attacks_port WHERE game_id = '.$player->getGameID().' AND sector_id = '.$player->getSectorID().' AND time > '.$allowed);
//				$temp = array();
//				while ($db->nextRecord()) {
//					$temp[$db->getField('account_id')] = $db->getField('level');
//				}
//				foreach ($temp as $tempAcc => $level) {
//					$db->query('UPDATE player_has_stats SET port_raids = port_raids + 1, port_raid_levels = port_raid_levels + '.$level.' ' . 
//								'WHERE account_id = '.$tempAcc.' AND game_id = '.$player->getGameID().' LIMIT 1');
//					$db->query('UPDATE account_has_stats SET port_raids = port_raids + 1, port_raid_levels = port_raid_levels + '.$level.' ' . 
//								'WHERE account_id = '.$tempAcc.' LIMIT 1');
//				}
//				$db->query('DELETE FROM player_attacks_port WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
//				unset($temp);
//				// News Entry
//				$news = '<span class="yellow smallCaps">Port ' . $player->getSectorID() . '</span> has been successfully raided by ';
//				if ($player->getAllianceID()) $news .= 'the members of <span class="yellow">' . $player->getAllianceName() . '</span>';
//				else $news .= '<span class="yellow">' . $player->getPlayerName() . '</span>';
//				$news = mysql_real_escape_string($news);
//				$db->query('INSERT INTO news (game_id, time, news_message, type) VALUES ('.$player->getGameID().', ' . TIME . ', '.$db->escapeString($news).', \'regular\')');
//				// Trigger gets an alignment change and a bounty if port is taken
//				$db->query('SELECT * FROM bounty WHERE game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID().' ' .
//					'AND claimer_id = 0 AND type = \'HQ\'');
//				$amount = $player->getExperience() * $port[PORT_LEVEL];
//				if ($db->nextRecord() && $amount > 0) {
//					$bounty_id = $db->getField('bounty_id');
//					$curr_amount = $db->getField('amount');
//					$new_amount = $curr_amount + $amount;
//					$db->query('UPDATE bounty SET amount = '.$new_amount.', time = ' . TIME . ' WHERE game_id = '.$player->getGameID().' AND bounty_id = '.$bounty_id);
//				} elseif ($amount > 0) {
//					$db->query('INSERT INTO bounty (account_id, game_id, type, amount, claimer_id, time) VALUES ' .
//						'('.$player->getAccountID().', '.$player->getGameID().', \'HQ\', '.$amount.', 0, ' . TIME . ')');
//				}
//				if($port[PORT_RACE_ID] > 1) {
//					$new_relations = $player->getRelation($port[PORT_RACE_ID]) - 45;
//					if ($new_relations < -500) $new_relations = -500;
//					$db->query('REPLACE INTO player_has_relation (account_id, game_id, race_id, relation) VALUES('.$player->getAccountID().', '.$player->getGameID().', ' . $port[PORT_RACE_ID] . ', '.$new_relations.')');
//				}
//				// also we change alignment
//				if ($player->relations_global_rev[$port[PORT_RACE_ID]] < -299)
//				   $new_alignment = $player->getAlignment() + $port[PORT_LEVEL] * 2;
//				else
//				   $new_alignment = $player->getAlignment() - $port[PORT_LEVEL] * 2;
//				$db->query('UPDATE player SET alignment='.$new_alignment.' WHERE account_id='.$player->getAccountID().' AND game_id='.$player->getGameID().' LIMIT 1');
//			}
//			$weapon++;
//		}
//		$totalPlayerDamage += $totalTraderDamage;
//		$db->query('UPDATE account_has_stats SET port_damage = port_damage + '.$totalTraderDamage.' WHERE account_id = '.$accId.' LIMIT 1');
//		$db->query('UPDATE player_has_stats SET port_damage = port_damage + '.$totalTraderDamage.' WHERE account_id = '.$accId.' AND game_id = '.$player->getGameID().' LIMIT 1');
//		$players[$accId][EXPERIENCE_GAINED] = round($totalTraderDamage * .05);
//		$traderDisplay .= '<br />' . $players[$accId][PLAYER_NAME] . ' does a total of <span class="red">'.$totalTraderDamage.'</span> damage.<br />';
//		//append this display to the overal display.
//		$playerDisplay .= $traderDisplay . '<br />';
//		$traderDisplay = '<span style="color:yellow;font-variant:small-caps">Port ' . $player->getSectorID() . '</span>\'s defenses ' . 
//							'do a total of <span class="red">'.$totalPortDamage.'</span> damage. <span class="red">' . 
//							$results[DMG_TO_PLAYER][$accId] . '</span> of which hit you.<br /><br />' . $traderDisplay . 
//							'You have gained <span class="blue">' . round($totalTraderDamage * .05) . '</span> experience.';
//		if ($accId != $player->getAccountID())
//			$db->query('REPLACE INTO sector_message (account_id, game_id, message) VALUES ('.$accId.', '.$player->getGameID().', ' . $db->escape_string($traderDisplay) . ')');
//	}
//	$playerDisplay .= 'This team does a total of <span class="red">'.$totalPlayerDamage.'</span> damage.<br />';
//	$results[PLAYER_DISPLAY] = $playerDisplay;
//	$results[TOTAL_PLAYER_DMG] = $totalPlayerDamage;
//	//we need to update the database for the players
//	$temp = array();
//	$ships = array();
//	$hqs = array();
//	foreach ($players as $accId => $playerArray) {
//		if ($playerArray[SHIELDS] == 0 && $playerArray[ARMOR] == 0) {
//			$temp[] = $accId;			
//			$ships[] = $players[$accId][SHIP_ID];
//			$hqs[] = $players[$accId][RACE_ID];
//		} else {
//			$db->query('UPDATE ship_has_hardware SET amount=' . $players[$accId][SHIELDS] . ' WHERE hardware_type_id=1 AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 1');
//			$db->query('UPDATE ship_has_hardware SET amount=' . $players[$accId][ARMOR] . ' WHERE hardware_type_id=2 AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 1');
//			$db->query('UPDATE ship_has_hardware SET amount=' . $players[$accId][DRONES] . ' WHERE hardware_type_id=4 AND account_id=' . $accId . ' AND game_id=' . $player->getGameID() . ' LIMIT 1');
//			$db->query('UPDATE player SET experience = experience + ' . $players[$accId][EXPERIENCE_GAINED] . ', credits = ' . $players[$accId][CREDITS] . ' WHERE account_id = '.$accId.' AND game_id = '.$player->getGameID());
//		}
//	}
//	podPlayers($temp, $ships, $hqs, $port, $players);
//	unset($temp);
//	return $results;
//}
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
//function portDowngrade(&$results, &$port) {
//	if (DEBUG) $PHP_OUTPUT.=('Downgrading<br />');
//	global $db, $player;
//	if ($port[PORT_LEVEL] > 1) {
//		$numberOfChances = floor($results[TOTAL_PLAYER_DMG] / 500);
//		for ($i = 0; $i < $numberOfChances; $i++) {
//			if (mt_rand(1, 100) <= 5) {
//				$db->query('SELECT count(*) as numGoods FROM port_has_goods WHERE game_id = '.$player->getGameID().' AND sector_id = '.$player->getSectorID());
//				$db->nextRecord();
//				if ($db->getField('numGoods') >= 3) {
//	
//		            // get last good for this port
//	    	        $db->query('SELECT good_id FROM port_has_goods ' .
//	                         'WHERE game_id = '.$player->getGameID().' AND ' .
//	                               'sector_id = '.$player->getSectorID().' ' .
//	                               'ORDER BY good_id DESC ' .
//	                               'LIMIT 1');
//	        	    if ($db->nextRecord())
//	            	    $good_id = $db->getField('good_id');
//		            // delete it from db
//	    	        $db->query('DELETE FROM port_has_goods ' .
//	                         'WHERE game_id = '.$player->getGameID().' AND ' .
//	                               'sector_id = '.$player->getSectorID().' AND ' .
//	                               'good_id = '.$good_id.' LIMIT 1');
//		        }
//		        $port[PORT_LEVEL] -= 1;
//				$results[PLAYER_DISPLAY] .= '<br />The port has lost a level.';
//				//only one downgrade per shot
//				$i = $numberOfChances;
//			}
//	   }	
//	}
//    $db->query('UPDATE port SET shields = ' . $port[PORT_SHIELDS] . ', armor = ' . $port[PORT_ARMOR] . ', combat_drones = ' . $port[PORT_DRONES] .
//    			', level = ' . $port[PORT_LEVEL] . ', credits = ' . $port[PORT_CREDITS] . ', attack_started = ' . $port[STARTED] . ', reinforce_time = ' . $port[REFRESH] .
//    			' WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
//}
//function doLog($results) {
//	if (DEBUG) $PHP_OUTPUT.=('Logging<br />');
//	global $account, $player;
//	$account->log(7, 'Player attacks port their team does ' . $results[TOTAL_PLAYER_DMG], $player->getSectorID());
//}
//function checkContinue($players, $port) {
//	if (DEBUG) $PHP_OUTPUT.=('Continue?<br />');
//	global $player;
//	if ($players[$player->getAccountID()][KILLER]) return FALSE;
//	if ($port[PORT_ARMOR] == 0) return FALSE;
//	return TRUE;
//}
//if (DEBUG) $PHP_OUTPUT.=('Opening<br />');
//if (!sizeof($players[$player->getAccountID()][WEAPONS])) create_error('What are you going to do?  Insult it to death?');
//$port = getPortArray();
//if (DEBUG) $PHP_OUTPUT.=('Pre news<br />');
//hofTracker($players, $port);
//// fire shots
//if (DEBUG) $PHP_OUTPUT.=('Pre Shots<br />');
//portFires($fleet,$port,$players);
//fleetFires($fleet,$port,$players,$weapons);
////get results in a way we want them
//$results = processResults($players, $port, $fleet, $weapons);
////post on alliances MB or send to player
//portDowngrade($results, $port);
//sendReport($results, $port);
////log player
//doLog($results);
////insert into combat logs
//$finalResults = $results[0] . '<br /><img src="images/portAttack.jpg" width="480px" height="330px" alt="Port Attack" title="Port Attack"><br />' . $results[1];
//$db->query('INSERT INTO combat_logs VALUES(\'\',' . SmrSession::$game_id . ',\'PORT\',' . $player->getSectorID() . ',' . TIME . ',' . SmrSession::$account_id . ',' . $player->getAllianceID() . ',0,0,' . $db->escape_string(gzcompress($finalResults)) . ', \'FALSE\')');
//if (DEBUG) $PHP_OUTPUT.=('Pre Forward/Display<br />');
//$container=array();
//$container['url'] = 'skeleton.php';
//$container['body'] = 'port_attack_new.php';
//$container['results'] = $results;
//if ($players[$player->getAccountID()][KILLER]) $container['override_death'] = TRUE;
//$container['continue'] = checkContinue($players, $port);
//forward($container);

?>