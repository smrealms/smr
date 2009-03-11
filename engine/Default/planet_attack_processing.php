<?

if ($player->hasNewbieTurns())
	create_error('You are under newbie protection!');
if ($player->hasFederalProtection())
	create_error('You are under federal protection!');
if($player->isLandedOnPlanet())
	create_error('You cannot attack planets whilst on a planet!');
if ($player->getTurns() < 3)
	create_error('You do not have enough turns to attack this planet!');
if(!$ship->hasWeapons() && !$ship->hasCDs())
	create_error('What are you going to do? Insult it to death?');
if(!$player->canFight())
	create_error('You are not allowed to fight!');

require_once(get_file_loc('SmrPlanet.class.inc'));
$planet =& SmrPlanet::getPlanet($player->getGameID(), $player->getSectorID());
if(!$planet->exists())
	create_error('This planet does not exist.');
if(!$planet->isClaimed())
	create_error('This planet is not claimed.');
	
$planetOwner =& $planet->getOwner();

if($player->forceNAPAlliance($planetOwner))
	create_error('You have a planet NAP, you cannot attack this planet!');
	
if ($planet->isDestroyed())
{
	$db->query('UPDATE player SET land_on_planet = \'FALSE\' WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
	$planet->removeClaimed();
	$planet->removePassword();
	$container=create_container('skeleton.php','planet_attack.php');
	$container['sector_id'] = $planet->getSectorID();
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
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());
$attackers =& $sector->getFightingTradersAgainstPlanet($player, $planet);

$planet->attackedBy($player,$attackers);

//decloak all attackers
foreach($attackers as &$attacker)
{
	$attacker->getShip()->decloak();
} unset($attacker);

foreach($attackers as &$attacker)
{
	$playerResults =& $attacker->shootPlanet($planet);
	$results['Attackers']['Traders'][$attacker->getAccountID()]  =& $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($attacker);
$account->log(7, 'Player attacks planet their team does ' . $results['Attackers']['TotalDamage'], $planet->getSectorID());
$results['Attackers']['Downgrades'] = $planet->checkForDowngrade($results['Attackers']['TotalDamage']);

$results['Planet'] =& $planet->shootPlayers($attackers);

$ship->removeUnderAttack(); //Don't show attacker the under attack message.
$planet->update();

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $player->getGameID() . ',\'PLANET\',' . $player->getSectorID() . ',' . TIME . ',' . $player->getAccountID() . ',' . $player->getAllianceID() . ','.$planetOwner->getAccountID().',' . $planetOwner->getAllianceID() . ',' . $db->escape_string(gzcompress($serializedResults)) . ', \'FALSE\')');
unserialize($serializedResults); //because of references we have to undo this.

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'planet_attack.php';
$container['sector_id'] = $planet->getSectorID();

// If they died on the shot they get to see the results
if($player->isDead())
{
	$container['override_death'] = TRUE;
}

$container['results'] = $serializedResults;
forward($container);



//function sendReport($results, $planet) {
//	if (DEBUG) $PHP_OUTPUT.=('Sending Reports<br />');
//	global $player, $db;
//	$db->query('SELECT * FROM player WHERE account_id = ' . $planet[OWNER] . ' AND game_id = '.$player->getGameID().' LIMIT 1');
//	$db->nextRecord();
//	$ownerAlliance = $db->getField('alliance_id');
//	$db->query('SELECT * FROM planet WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
//	$db->nextRecord();
//	$planetName = '<span style="color:yellow;font-variant:small-caps">' . stripslashes($db->getField('planet_name')) . '</span>';
//	$mainText = 'From the reports we have been able to gather the following information:<br /><br />';
//	$mainText .= $results[PLANET_DISPLAY] . '<br />' . $results[PLAYER_DISPLAY];
//	if ($ownerAlliance > 0) {
//		$topic = 'Planet Attack Report Sector '.$player->getSectorID();
//		$text = 'Reports from the surface of '.$planetName.' confirm that it is under <span class="red">attack</span>!<br />';
//		$text .= $mainText;
//		$text = mysql_real_escape_string($text);
//		$thread_id = 0;
//		$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$ownerAlliance.' AND topic = '.$db->escapeString($topic).' LIMIT 1');
//		if ($db->nextRecord()) $thread_id = $db->getField('thread_id');
//		if ($thread_id == 0)
//		{
//			$db->query('SELECT * FROM alliance_thread_topic WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$ownerAlliance.' ORDER BY thread_id DESC LIMIT 1');
//			if ($db->nextRecord())
//				$thread_id = $db->getField('thread_id') + 1;
//			else $thread_id = 1;
//			$db->query('INSERT INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ' .
//						'('.$player->getGameID().', '.$ownerAlliance.', '.$thread_id.', '.$db->escapeString($topic).')');
//		}
//		$db->query('SELECT * FROM alliance_thread WHERE alliance_id = '.$ownerAlliance.' AND game_id = '.$player->getGameID().' AND ' .
//					'thread_id = '.$thread_id.' ORDER BY reply_id DESC LIMIT 1');
//		if ($db->nextRecord()) $reply_id = $db->getField('reply_id') + 1;
//		else $reply_id = 1;
//		$db->query('INSERT INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES ' .
//				'('.$player->getGameID().', '.$ownerAlliance.', '.$thread_id.', '.$reply_id.', '.$db->escapeString($text).', 0, ' . TIME . ')');
//		$db->query('SELECT * FROM player WHERE alliance_id = '.$ownerAlliance.' AND game_id = '.$player->getGameID());
//		while ($db->nextRecord())
//			$temp[] = $db->getField('account_id');
//		foreach ($temp as $tempAccId) {
//			$db->query('INSERT INTO player_has_unread_messages (account_id, game_id, message_type_id) VALUES ' .
//						'('.$tempAccId.', '.$player->getGameID().', 3)');
//		}
//	} else {
//		$text = 'Reports from the surface of '.$planetName.' confirm that it is under <span class="red">attack</span>!<br />';
//		$text .= $mainText;
//		$text = mysql_real_escape_string($text);
//		$db->query('INSERT INTO message (game_id, account_id, message_type_id, message_text, sender_id, send_time) VALUES ' .
//					'('.$player->getGameID().', ' . $planet[OWNER] . ', 3, '.$db->escapeString($text).', 0, ' . TIME . ')');
//		$db->query('INSERT INTO player_has_unread_messages (account_id, game_id, message_type_id) VALUES ' .
//						'(' . $planet[OWNER] . ', '.$player->getGameID().', 3)');
//	} if ($player->getAllianceID() > 0) {
//		$topic = 'Planet Siege Report Sector '.$player->getSectorID();
//		$text = 'Reports have come in from the space above '.$planetName.' and have confirmed our <span class="red">siege</span>!<br />';
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