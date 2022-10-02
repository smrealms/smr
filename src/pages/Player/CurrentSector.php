<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$sector = $player->getSector();

// If on a planet, forward to planet_main.php
if ($player->isLandedOnPlanet()) {
	Page::create('planet_main.php', $var)->go();
}

$template->assign('SpaceView', true);

$template->assign('PageTopic', 'Current Sector: ' . $player->getSectorID() . ' (' . $sector->getGalaxy()->getDisplayName() . ')');

Menu::navigation($player);


// *******************************************
// *
// * Sector List
// *
// *******************************************

// Sector links
$links = [];
$links['Up'] = ['ID' => $sector->getLinkUp()];
$links['Right'] = ['ID' => $sector->getLinkRight()];
$links['Down'] = ['ID' => $sector->getLinkDown()];
$links['Left'] = ['ID' => $sector->getLinkLeft()];
$links['Warp'] = ['ID' => $sector->getWarp()];

$unvisited = [];

$db = Database::getInstance();
$dbResult = $db->read('SELECT sector_id FROM player_visited_sector WHERE sector_id IN (' . $db->escapeArray($links) . ') AND ' . $player->getSQL());
foreach ($dbResult->records() as $dbRecord) {
	$unvisited[$dbRecord->getInt('sector_id')] = true;
}

foreach ($links as $key => $linkArray) {
	if ($linkArray['ID'] > 0 && $linkArray['ID'] != $player->getSectorID()) {
		if ($player->getLastSectorID() == $linkArray['ID']) {
			$class = 'lastVisited';
		} elseif (isset($unvisited[$linkArray['ID']])) {
			$class = 'unvisited';
		} else {
			$class = 'visited';
		}
		$links[$key]['Class'] = $class;
	}
}

$template->assign('Sectors', $links);

doTickerAssigns($template, $player, $db);

if (!isset($var['UnreadMissions'])) {
	$var['UnreadMissions'] = $player->markMissionsRead();
}
$template->assign('UnreadMissions', $var['UnreadMissions']);

// *******************************************
// *
// * Force and other Results
// *
// *******************************************
$game = SmrGame::getGame($player->getGameID());
if (!$game->hasStarted()) {
	$turnsMessage = 'The game will start in ' . format_time($game->getStartTime() - Epoch::time()) . '!';
} else {
	$turnsMessage = $player->getTurnsLevel()->message();
}
$template->assign('TurnsMessage', $turnsMessage);

$protectionMessage = '';
if ($player->getNewbieTurns()) {
	if ($player->getNewbieTurns() < 25) {
		$protectionMessage = '<span class="blue">PROTECTION</span>: You are almost out of <span class="green">NEWBIE</span> protection.';
	} else {
		$protectionMessage = '<span class="blue">PROTECTION</span>: You are under <span class="green">NEWBIE</span> protection.';
	}
} elseif ($player->hasFederalProtection()) {
	$protectionMessage = '<span class="blue">PROTECTION</span>: You are under <span class="blue">FEDERAL</span> protection.';
} elseif ($sector->offersFederalProtection()) {
	$protectionMessage = '<span class="blue">PROTECTION</span>: You are <span class="red">NOT</span> under protection.';
}

if (!empty($protectionMessage)) {
	$template->assign('ProtectionMessage', $protectionMessage);
}

//enableProtectionDependantRefresh($template,$player);

// Do we have an unseen attack message to store in this var?
$dbResult = $db->read('SELECT * FROM sector_message WHERE ' . $player->getSQL());
if ($dbResult->hasRecord()) {
	$var['AttackMessage'] = $dbResult->record()->getString('message');
	$db->write('DELETE FROM sector_message WHERE ' . $player->getSQL());
}

if (isset($var['AttackMessage'])) {
	checkForAttackMessage($var['AttackMessage'], $player);
}
if (isset($var['showForceRefreshMessage'])) {
	$template->assign('ForceRefreshMessage', getForceRefreshMessage($player));
}
if (isset($var['MissionMessage'])) {
	$template->assign('MissionMessage', $var['MissionMessage']);
}
if (isset($var['msg'])) {
	$template->assign('VarMessage', bbifyMessage($var['msg']));
}

//error msgs take precedence
if (isset($var['errorMsg'])) {
	$template->assign('ErrorMessage', $var['errorMsg']);
}

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

if ($sector->hasPort()) {
	$port = $sector->getPort();
	$template->assign('PortIsAtWar', $player->getRelation($port->getRaceID()) < RELATIONS_WAR);
}

// *******************************************
// *
// * Ships
// *
// *******************************************
$otherPlayers = $sector->getOtherTraders($player);
$visiblePlayers = [];
$cloakedPlayers = [];
foreach ($otherPlayers as $accountID => $otherPlayer) {
	if ($player->canSee($otherPlayer)) {
		$visiblePlayers[$accountID] = $otherPlayer;
	} else {
		$cloakedPlayers[$accountID] = $otherPlayer;
	}
}
$template->assign('VisiblePlayers', $visiblePlayers);
$template->assign('CloakedPlayers', $cloakedPlayers);
$template->assign('SectorPlayersLabel', 'Ships');


function getForceRefreshMessage(AbstractSmrPlayer $player): string {
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT refresh_at FROM sector_has_forces WHERE refresh_at > ' . $db->escapeNumber(Epoch::time()) . ' AND sector_id = ' . $db->escapeNumber($player->getSectorID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND refresher = ' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY refresh_at DESC LIMIT 1');
	if ($dbResult->hasRecord()) {
		$remainingTime = $dbResult->record()->getInt('refresh_at') - Epoch::time();
		$forceRefreshMessage = '<span class="green">REFRESH</span>: All forces will be refreshed in ' . $remainingTime . ' seconds.';
	} else {
		$forceRefreshMessage = '<span class="green">REFRESH</span>: All forces have finished refreshing.';
	}
	return $forceRefreshMessage;
}

function checkForAttackMessage(string $msg, AbstractSmrPlayer $player): void {
	$contains = 0;
	$msg = str_replace('[ATTACK_RESULTS]', '', $msg, $contains);
	if ($contains > 0) {
		// $msg now contains only the log_id, if there is one
		$logID = str2int($msg);

		$template = Smr\Template::getInstance();
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT sector_id,result,type FROM combat_logs WHERE log_id=' . $db->escapeNumber($logID) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			if ($player->getSectorID() == $dbRecord->getInt('sector_id')) {
				$results = $dbRecord->getObject('result', true);
				$template->assign('AttackResultsType', $dbRecord->getString('type'));
				$template->assign('AttackResults', $results);
				$template->assign('AttackLogLink', linkCombatLog($logID));
			}
		}
	}
}
