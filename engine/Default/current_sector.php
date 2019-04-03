<?php

// If on a planet, forward to planet_main.php
if($player->isLandedOnPlanet()) {
	forward(create_container('skeleton.php', 'planet_main.php', $var));
}

$template->assign('SpaceView',true);

$template->assign('PageTopic','Current Sector: ' . $player->getSectorID() . ' (' .$sector->getGalaxyName() . ')');

Menu::navigation($template, $player);


// *******************************************
// *
// * Sector List
// *
// *******************************************

// Sector links
$links = array();
$links['Up'] = array('ID'=>$sector->getLinkUp());
$links['Right'] = array('ID'=>$sector->getLinkRight());
$links['Down'] = array('ID'=>$sector->getLinkDown());
$links['Left'] = array('ID'=>$sector->getLinkLeft());
$links['Warp'] = array('ID'=>$sector->getWarp());

$unvisited = array();

$db->query('SELECT sector_id FROM player_visited_sector WHERE sector_id IN (' . $db->escapeString($links,false) . ') AND account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()));
while($db->nextRecord()) {
	$unvisited[$db->getField('sector_id')] = TRUE;
}

foreach($links as $key => $linkArray) {
	if($linkArray['ID']>0 && $linkArray['ID']!=$player->getSectorID()) {
		if ($player->getLastSectorID() == $linkArray['ID']) $class = 'lastVisited';
		else if(isset($unvisited[$linkArray['ID']])) $class = 'unvisited';
		else $class = 'visited';
		$links[$key]['Class']=$class;
	}
}

$template->assign('Sectors',$links);

doTickerAssigns($template, $player, $db);

if(!isset($var['UnreadMissions'])) {
	$unreadMissions = $player->markMissionsRead();
	SmrSession::updateVar('UnreadMissions', $unreadMissions);
}
$template->assign('UnreadMissions', $var['UnreadMissions']);

// *******************************************
// *
// * Force and other Results
// *
// *******************************************
$turnsMessage = '';
$game = SmrGame::getGame($player->getGameID());
if ($game->getStartTurnsDate() > TIME) {
	$turnsMessage = 'Turns will be given when the game starts in ' . format_time($game->getStartTurnsDate() - TIME) . '!';
} else {
	switch($player->getTurnsLevel()) {
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
}
$template->assign('TurnsMessage',$turnsMessage);

$protectionMessage = '';
if ($player->getNewbieTurns()) {
	if ($player->getNewbieTurns() < 25) {
		$protectionMessage = '<span class="blue">PROTECTION</span>: You are almost out of <span class="green">NEWBIE</span> protection.';
	}
	else
		$protectionMessage = '<span class="blue">PROTECTION</span>: You are under <span class="green">NEWBIE</span> protection.';
}
elseif ($player->hasFederalProtection()) {
	$protectionMessage = '<span class="blue">PROTECTION</span>: You are under <span class="blue">FEDERAL</span> protection.';
}
elseif($sector->offersFederalProtection())
	$protectionMessage = '<span class="blue">PROTECTION</span>: You are <span class="red">NOT</span> under protection.';

if(!empty($protectionMessage))
	$template->assign('ProtectionMessage',$protectionMessage);

//enableProtectionDependantRefresh($template,$player);

$db->query('SELECT * FROM sector_message WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
if ($db->nextRecord()) {
	$msg = $db->getField('message');
	$db->query('DELETE FROM sector_message WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	checkForForceRefreshMessage($msg);
	checkForAttackMessage($msg);
}
if (isset($var['AttackMessage'])) {
	$msg = $var['AttackMessage'];
	checkForAttackMessage($msg);
}
if (isset($var['MissionMessage'])) {
	$template->assign('MissionMessage', $var['MissionMessage']);
}
if (isset($var['msg'])) {
	checkForForceRefreshMessage($var['msg']);
	$template->assign('VarMessage', bbifyMessage($var['msg']));
}

//error msgs take precedence
if (isset($var['errorMsg'])) $template->assign('ErrorMessage', $var['errorMsg']);

// *******************************************
// *
// * Trade Result
// *
// *******************************************

if (!empty($var['trade_msg'])) {
	$template->assign('TradeMessage', $var['trade_msg']);
}

// *******************************************
// *
// * Ports
// *
// *******************************************

if($sector->hasPort()) {
	$port = $sector->getPort();
	$template->assign('PortIsAtWar',$player->getRelation($port->getRaceID()) < RELATIONS_WAR);
}

function checkForForceRefreshMessage(&$msg) {
	global $db,$player,$template;
	$contains = 0;
	$msg = str_replace('[Force Check]','',$msg,$contains);
	if($contains>0) {
		if(!$template->hasTemplateVar('ForceRefreshMessage')) {
			$forceRefreshMessage ='';
			$db->query('SELECT refresh_at FROM sector_has_forces WHERE refresh_at >= ' . $db->escapeNumber(TIME) . ' AND sector_id = ' . $db->escapeNumber($player->getSectorID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND refresher = ' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY refresh_at DESC LIMIT 1');
			if ($db->nextRecord()) {
				$remainingTime = $db->getField('refresh_at') - TIME;
				$forceRefreshMessage = '<span class="green">REFRESH</span>: All forces will be refreshed in '.$remainingTime.' seconds.';
				$db->query('REPLACE INTO sector_message (game_id, account_id, message) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAccountID()) . ', \'[Force Check]\')');
			}
			else $forceRefreshMessage = '<span class="green">REFRESH</span>: All forces have finished refreshing.';
			$template->assign('ForceRefreshMessage',$forceRefreshMessage);
		}
	}
}

function checkForAttackMessage(&$msg) {
	global $db,$player,$template;
	$contains = 0;
	$msg = str_replace('[ATTACK_RESULTS]','',$msg,$contains);
	if($contains>0) {
		// $msg now contains only the log_id, if there is one
		SmrSession::updateVar('AttackMessage','[ATTACK_RESULTS]'.$msg);
		if(!$template->hasTemplateVar('AttackResults')) {
			$db->query('SELECT sector_id,result,type FROM combat_logs WHERE log_id=' . $db->escapeNumber($msg) . ' LIMIT 1');
			if($db->nextRecord()) {
				if($player->getSectorID()==$db->getField('sector_id')) {
					$results = unserialize(gzuncompress($db->getField('result')));
					$template->assign('AttackResultsType',$db->getField('type'));
					$template->assign('AttackResults',$results);
					$template->assign('AttackLogLink', linkCombatLog($msg));
				}
			}
		}
	}
}
