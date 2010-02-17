<?php
if($player->isLandedOnPlanet())
	create_error('You are on a planet!');

require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());

$template->assign('ThisSector',$sector);
$template->assign('SpaceView',true);

$template->assign('PageTopic','Current Sector: ' . $player->getSectorID() . ' (' .$sector->getGalaxyName() . ')');

require_once(get_file_loc('menue.inc'));
create_nav_menue($template,$player);


// *******************************************
// *
// * Sector List
// *
// *******************************************

// Sector links
$db->query('SELECT sector_id,link_up,link_right,link_down,link_left FROM sector WHERE sector_id=' . $player->getSectorID() . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');

$db->nextRecord();
$links = array();
$links['Up'] = array('ID'=>$sector->getLinkUp());
$links['Right'] = array('ID'=>$sector->getLinkRight());
$links['Down'] = array('ID'=>$sector->getLinkDown());
$links['Left'] = array('ID'=>$sector->getLinkLeft());
$links['Warp'] = array('ID'=>$sector->getWarp());

$unvisited = array();

$db->query('SELECT sector_id FROM player_visited_sector WHERE sector_id IN (' . $db->escapeString($links,false) . ') AND account_id=' . SmrSession::$account_id . ' AND game_id=' . SmrSession::$game_id);
while($db->nextRecord())
{
	$unvisited[$db->getField('sector_id')] = TRUE;
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
		if ($player->getLastSectorID() == $linkArray['ID']) $class = 'lastVisited';
		else if(isset($unvisited[$linkArray['ID']])) $class = 'unvisited';
		else $class = 'visited';
		$links[$key]['Class']=$class;
		
		if($ship->hasScanner())
			$links[$key]['ScanLink']=SmrSession::get_new_href($container2);
	}
}

$template->assign('Sectors',$links);


//any ticker news?
if($player->hasTickers())
{
	$ticker = array();
	$max = TIME - 60;
	if($player->hasTicker('NEWS'))
	{
		$text = '';
		//get recent news (5 mins)
		
		$db->query('SELECT time,news_message FROM news WHERE game_id = '.$player->getGameID().' AND time >= '.$max.' ORDER BY time DESC LIMIT 4');
		if ($db->getNumRows())
		{
			while($db->nextRecord())
			{
				$ticker[] = array('Time' => date(DATE_FULL_SHORT, $db->getField('time')),
								'Message'=>$db->getField('news_message'));
			}
		}
	}
	if ($player->hasTicker('SCOUT'))
	{
		$text = '';
		//get people who have blockers
		$db->query('SELECT * FROM player_has_ticker WHERE type='.$db->escapeString('BLOCK').' AND expires > '. TIME .' AND game_id = ' . $player->getGameID());
		$temp=array();
		$temp[] = 0;
		while ($db->nextRecord()) $temp[] = $db->getField('account_id');
		$query = 'SELECT message_text,send_time FROM message
					WHERE account_id=' . $player->getAccountID() . '
					AND game_id=' . $player->getGameID() . '
					AND message_type_id=' . MSG_SCOUT . '
					AND send_time>=' . $max . '
					AND sender_id NOT IN (' . implode(',', $temp) . ') AND reciever_delete = \'FALSE\'
					ORDER BY send_time DESC
					LIMIT 4';
		$db->query($query);
		unset($temp);
		if ($db->getNumRows())
		{
			while($db->nextRecord())
			{
				$ticker[] = array('Time' => date(DATE_FULL_SHORT, $db->getField('send_time')),
								'Message'=>$db->getField('message_text'));
			}
		}
	}
	$template->assign('Ticker',$ticker);
}

// *******************************************
// *
// * Force and other Results
// *
// *******************************************
$turnsMessage = '';
switch($player->getTurnsLevel())
{
	case 'NONE':
		$turnsMessage = '<span class="red">WARNING</span>: You have run out of turns!';
	break;
	case 'LOW':
		$turnsMessage = '<span class="red">WARNING</span>: You are almost out of turns!';
	break;
	case 'MEDIUM':
		$turnsMessage = '<span class="yellow">WARNING</span>: You are running out of turns!';
	break;
}

$template->assign('TurnsMessage',$turnsMessage);

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
elseif($sector->offersFederalProtection())
	$protectionMessage = '<span class="blue">PROTECTION</span>: You are <span class="red">NOT</span> under protection.';

if(!empty($protectionMessage))
	$template->assign('ProtectionMessage',$protectionMessage);

//enableProtectionDependantRefresh($template,$player);

$db->query('SELECT * FROM sector_message WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
if ($db->nextRecord())
{
	$msg = $db->getField('message');
	$db->query('DELETE FROM sector_message WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
	checkForForceRefreshMessage($msg);
	checkForAttackMessage($msg);
}
if (isset($var['AttackMessage']))
{
  $msg = $var['AttackMessage'];
	checkForAttackMessage($msg);
}
if (isset($var['msg']))
{
	checkForForceRefreshMessage($var['msg']);
	$template->assign('VarMessage',$var['msg']);
}

//error msgs take precedence
if (isset($var['errorMsg'])) $template->assign('ErrorMessage', $var['errorMsg']);

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

	$tradeMessage = 'You have just ' . $var['traded_transaction'] . ' <span class="yellow">' .
		$var['traded_amount'] . '</span> units of <span class="yellow">' . $var['traded_good'] .
		'</span> for <span class="creds">' . $var['traded_credits'] . '</span> credits.<br />';

	if ($var['traded_xp'] > 0)
	{
		$tradeMessage .= 'Your excellent trading skills have gained you <span class="exp">' . $var['traded_xp'] . ' </span> experience points!<br />';
	}

	$tradeMessage .= '<br />';
	
	$template->assign('TradeMessage',$tradeMessage);
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
	$template->assign('TraderRelationsLink', SmrSession::get_new_href($container));
	
	$template->assign('PortRaceName',get_colored_text($player->getRelation($port->getRaceID()), $RACES[$port->getRaceID()]['Race Name']));
	
	$portRelations = Globals::getRaceRelations(SmrSession::$game_id,$port->getRaceID());
	$relations = $player->getRelation($port->getRaceID()) + $portRelations[$player->getRaceID()];
	$template->assign('PortIsAtWar',$relations <= -300);
}

function checkForForceRefreshMessage(&$msg)
{
	global $db,$player,$template;
	$contains = 0;
	$msg = str_replace('[Force Check]','',$msg,$contains);
	if($contains>0)
	{
		if(!$template->hasTemplateVar('ForceRefreshMessage'))
		{
			$forceRefreshMessage ='';
			$db->query('SELECT refresh_at FROM sector_has_forces WHERE refresh_at >= ' . TIME . ' AND sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID().' AND refresher = ' . $player->getAccountID() . ' ORDER BY refresh_at DESC LIMIT 1');
			if ($db->nextRecord())
			{
				$remainingTime = $db->getField('refresh_at') - TIME;
				$forceRefreshMessage = '<span class="green">REFRESH</span>: All forces will be refreshed in '.$remainingTime.' seconds.';
				$db->query('REPLACE INTO sector_message (game_id, account_id, message) VALUES ('.$player->getGameID().', '.$player->getAccountID().', \'[Force Check]\')');
			}
			else $forceRefreshMessage = '<span class="green">REFRESH</span>: All forces have finished refreshing.';
			$template->assign('ForceRefreshMessage',$forceRefreshMessage);
		}
	}
}

function checkForAttackMessage(&$msg)
{
	global $db,$player,$template;
	$contains = 0;
	$msg = str_replace('[ATTACK_RESULTS]','',$msg,$contains);
	if($contains>0)
	{
		SmrSession::updateVar('AttackMessage','[ATTACK_RESULTS]'.$msg);
		if(!$template->hasTemplateVar('AttackResults'))
		{
			$db->query('SELECT sector_id,result,type FROM combat_logs WHERE log_id=' . $db->escapeNumber($msg) . ' LIMIT 1');
			if($db->nextRecord())
			{
				if($player->getSectorID()==$db->getField('sector_id'))
				{
					$results = unserialize(gzuncompress($db->getField('result')));
					$template->assign('AttackResultsType',$db->getField('type'));
					$template->assignByRef('AttackResults',$results);
				}
			}
		}
	}
}
?>
