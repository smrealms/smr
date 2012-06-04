<?
if($player->isLandedOnPlanet())
	$PHP_OUTPUT.=create_echo_error('You are on a planet!');

require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

$smarty->assign('ThisSector',$sector);

// get our rank
$rank_id = $account->get_rank();

// add newbie to gal name?
$galaxyName = $sector->getGalaxyName();
if ($sector->getGalaxyID()<9 && $rank_id < FLEDGLING && $account->veteran == 'FALSE') {
	$galaxyName .= ' - Newbie';
}

$smarty->assign('Topic','CURRENT SECTOR: ' . $player->getSectorID() . ' (' .$galaxyName . ')');


// *******************************************
// *
// * Refresh Forces
// *
// *******************************************

$db->query('SELECT * FROM sector_has_forces WHERE sector_id = '.$player->getSectorID() .
		' AND game_id = '.$player->getGameID().' AND refresh_at <= '.TIME.' AND refresher = '.$player->getAccountID());
while ($db->next_record())
{	
	$total = $db->f('combat_drones')+$db->f('scout_drones')+$db->f('mines');
	$days = ceil($total / 10);
	if ($days > 5) $days = 5;
	$expire = $db->f('refresh_at') + ($days * 86400);
	$db->query('UPDATE sector_has_forces SET expire_time = '.$expire.' WHERE game_id = '.$db->f('game_id').' AND sector_id = '.$db->f('sector_id').' AND ' . 
				'owner_id = '.$db->f('owner_id').' LIMIT 1');
}

// *******************************************
// *
// * Sector List
// *
// *******************************************

// Sector links
$db->query('SELECT sector_id,link_up,link_right,link_down,link_left FROM sector WHERE sector_id=' . $player->getSectorID() . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');

$db->next_record();
$links = array();
$links['Up'] = array('ID'=>$sector->getLinkUp());
$links['Right'] = array('ID'=>$sector->getLinkRight());
$links['Down'] = array('ID'=>$sector->getLinkDown());
$links['Left'] = array('ID'=>$sector->getLinkLeft());
$links['Warp'] = array('ID'=>$sector->getLinkWarp());

$unvisited = array();

$db->query('SELECT sector_id FROM player_visited_sector WHERE sector_id IN (' . $db->escapeString($links,false) . ') AND account_id=' . SmrSession::$account_id . ' AND game_id=' . SmrSession::$game_id);
while($db->next_record())
{
	$unvisited[$db->f('sector_id')] = TRUE;
}

$container1 = array();
$container1['url'] = 'sector_move_processing.php';
$container1['target_page'] = 'current_sector.php';

$container2 = array();
$container2['url'] = 'skeleton.php';
$container2['body'] = 'sector_scan.php';

foreach($links as $key => $linkArray)
{
	if($linkArray['ID']>0 && $linkArray['ID']!=$player->getSectorID())
	{
		$container1['target_sector'] = $linkArray['ID'];
		$container2['target_sector'] = $linkArray['ID'];
		
		$links[$key]['MoveLink']=SmrSession::get_new_href($container1);
		if ($player->getLastSectorID() == $linkArray['ID']) $class = 'green';
		else if(isset($unvisited[$linkArray['ID']])) $class = 'yellow';
		else $class = 'dgreen';
		$links[$key]['Class']=$class;
		
		if($ship->hasScanner())
			$links[$key]['ScanLink']=SmrSession::get_new_href($container2);
	}
}

$smarty->assign('Sectors',$links);


//any ticker news?
if($player->hasTicker())
{
	$ticker = array();
	$max = time() - 60;
	if($player->ticker == 'NEWS')
	{
		$text = '';
		//get recent news (5 mins)
		
		$db->query('SELECT time,news_message FROM news WHERE game_id = '.$player->getGameID().' AND time >= '.$max.' ORDER BY time DESC LIMIT 4');
		if ($db->nf()) {
			while($db->next_record())
			{
				$ticker[] = array('Time' => date('n/j/Y g:i:s A', $db->f('time')),
								'Message'=>$db->f('news_message'));
			}
		}
	}
	else if ($player->ticker=='SCOUT')
	{
		$text = '';
		//get people who have blockers
		$db->query('SELECT * FROM player_has_ticker WHERE type=\'block\' AND game_id = ' . $player->getGameID());
		$temp=array();
		$temp[] = 0;
		while ($db->next_record()) $temp[] = $db->f('account_id');
		$query = 'SELECT message_text,send_time FROM message
					WHERE account_id=' . $player->getAccountID() . '
					AND game_id=' . $player->getGameID() . '
					AND message_type_id=' . $SCOUTMSG . '
					AND send_time>=' . $max . '
					AND sender_id NOT IN (' . implode(',', $temp) . ')
					ORDER BY send_time DESC
					LIMIT 4';
		$db->query($query);
		unset($temp);
		if ($db->nf()) {
			while($db->next_record()){
				$ticker[] = array('Time' => date('n/j/Y g:i:s A', $db->f('send_time')),
								'Message'=>$db->f('message_text'));
			}
		}
	}
	$smarty->assign('Ticker',$ticker);
	$player->last_ticker_update = TIME;
	$player->update();
}

// *******************************************
// *
// * Force and other Results
// *
// *******************************************
$turnsMessage = '';
if ($player->getTurnsLevel() == 'LOW')
	$turnsMessage = '<span class="red">WARNING</span>: You are almost out of maintenance!';
if ($player->getTurnsLevel() == 'MEDIUM')
	$turnsMessage = '<span class="yellow">WARNING</span>: You are running out of maintenance!';

$smarty->assign('TurnsMessage',$turnsMessage);

$protectionMessage = '';
if ($player->getNewbieTurns())
{
	if ($player->getNewbieTurns() < 25)
	{
		$protectionMessage = '<span class="blue">PROTECTION</span>: You are almost out of <span class="green">NEWBIE</span> protection.';
	}
	else
		$protectionMessage = '<span class="blue">PROTECTION</span>: You are under <span class="green">NEWBIE</span> protection.';
}
elseif ($player->hasFederalProtection())
{
	$protectionMessage = '<span class="blue">PROTECTION</span>: You are under <span class="blue">FEDERAL</span> protection.';
}
else
	$protectionMessage = '<span class="blue">PROTECTION</span>: You are <span class="red">NOT</span> under protection.';

$smarty->assign('ProtectionMessage',$protectionMessage);


$db->query('SELECT * FROM sector_message WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
if ($db->next_record())
{
	$msg = $db->f('message');
	$db->query('DELETE FROM sector_message WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
	if ($msg == '[Force Check]')
	{
		$forceRefreshMessage ='';
		$db->query('SELECT * FROM force_refresh WHERE refresh_at >= ' . time() . ' AND sector_id  = '.$player->getSectorID().' AND game_id = '.$player->getGameID().' ORDER BY refresh_at DESC LIMIT 1');
		if ($db->next_record()) {
			$remainingTime = $db->f('refresh_at') - time();
			$forceRefreshMessage = '<span class="green">REFRESH</span>: All forces will be refreshed in '.$remainingTime.' seconds.';
			$db->query('REPLACE INTO sector_message (game_id, account_id, message) VALUES ('.$player->getGameID().', '.$player->getAccountID().', \'[Force Check]\')');
		}
		else $forceRefreshMessage = '<span class="green">REFRESH</span>: All forces have finished refreshing.';
		$smarty->assign('ForceRefreshMessage',$forceRefreshMessage);
	}
}
if (isset($var['msg']))	$smarty->assign('VarMessage',$var['msg']);



if ($player->getAccountID() == 2)
{
	$db->query('SELECT * FROM player WHERE account_id = 10106 AND game_id = 23 LIMIT 1');
	if ($db->next_record()) $msg .= '<br />In Sector:'.$db->f('sector_id').'<br />';
}
//error msgs take precedence
if (isset($var['errorMsg'])) $smarty->assign('ErrorMessage', $var['errorMsg']);

// *******************************************
// *
// * Trade Result
// *
// *******************************************

//You have sold 300 units of Luxury Items for 1738500 credits. For your excellent trading skills you receive 220 experience points!
if (!empty($var['traded_xp']) ||
	!empty($var['traded_amount']) ||
	!empty($var['traded_good']) ||
	!empty($var['traded_credits'])) {

	$tradeMessage = 'You have just ' . $var['traded_transaction'] . ' <span style="color:yellow;">' .
		$var['traded_amount'] . '</span> units of <span style="color:yellow;">' . $var['traded_good'] .
		'</span> for <span style="color:yellow;">' . $var['traded_credits'] . '</span> credits.<br />';

	if ($var['traded_xp'] > 0)
	{
		$tradeMessage .= 'Your excellent trading skills have gained you <font color="blue">' . $var['traded_xp'] . ' </font>experience points!<br />';
	}

	$tradeMessage .= '<br />';
	
	$smarty->assign('TradeMessage',$tradeMessage);
}


// *******************************************
// *
// * Ports
// *
// *******************************************

if($sector->hasPort())
{
	// We need the races later for the players, so pull them out here
	$RACES = Globals::getRaces();
	
	// Cache good names for later
	$GOODS = Globals::getGoods();
	
	$port =& SmrPort::getPort($player->getGameID(),$player->getSectorID());
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'trader_relations.php';
	$smarty->assign('TraderRelationsLink', SmrSession::get_new_href($container));
	
	$smarty->assign('PortRaceName',get_colored_text($player->getRelation($port->getRaceID()), $RACES[$port->getRaceID()]['Race Name']));
	
	$portRelations = Globals::getRaceRelations(SmrSession::$game_id,$port->getRaceID());
	$relations = $player->getRelation($port->getRaceID()) + $portRelations[$player->getRaceID()];
	$smarty->assign('PortIsAtWar',$relations <= -300);
}
?>
