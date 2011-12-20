<?php
$template->assign('PageTopic','Combat Simulator');

require_once(get_file_loc('DummyPlayer.class.inc'));

$template->assign('EditDummysLink',SmrSession::get_new_href(create_container('skeleton.php','edit_dummys.php')));
$template->assign('DummyNames', DummyPlayer::getDummyPlayerNames());

$duplicates = false;
$usedNames = array();
$realAttackers = array();
$attackers = array();
$i=1;
if(isset($_POST['attackers']))
	foreach($_POST['attackers'] as $attackerName) {
		if($attackerName=='none')
			continue;
		if(isset($usedNames[$attackerName])) {
			$duplicates = true;
			continue;
		}
		$usedNames[$attackerName] = true;
		$attackers[$i] =& DummyPlayer::getCachedDummyPlayer($attackerName);
		$attackers[$i]->setAllianceID(1);
		$realAttackers[$i] =& $attackers[$i];
		++$i;
	}

for(;$i<=10;++$i)
	$attackers[$i] = null;
$template->assignByRef('Attackers',$attackers);

$i=1;
$realDefenders = array();
$defenders = array();
if(isset($_POST['defenders']))
	foreach($_POST['defenders'] as $defenderName) {
		if($defenderName=='none')
			continue;
		if(isset($usedNames[$defenderName])) {
			$duplicates = true;
			continue;
		}
		$usedNames[$attackerName] = true;
		$defenders[$i] =& DummyPlayer::getCachedDummyPlayer($defenderName);
		$defenders[$i]->setAllianceID(2);
		$realDefenders[$i] =& $defenders[$i];
		++$i;
	}
	
for(;$i<=10;++$i)
	$defenders[$i] = null;
$template->assignByRef('Defenders',$defenders);

$template->assign('Duplicates',$duplicates);

$template->assign('CombatSimHREF',SmrSession::get_new_href(create_container('skeleton.php','combat_simulator.php')));

if(is_array($realAttackers) && is_array($realDefenders) && count($realAttackers)>0 && count($realDefenders)>0) {
	if(isset($_REQUEST['run'])) {
		runAnAttack($realAttackers,$realDefenders);
	}
	if(isset($_REQUEST['death_run'])) {
		while(count($realAttackers)>0 && count($realDefenders)>0) {
			runAnAttack($realAttackers,$realDefenders);
			foreach($realAttackers as $key => &$teamPlayer) {
				if($teamPlayer->isDead())
					unset($realAttackers[$key]);
			} unset($teamPlayer);
			foreach($realDefenders as $key => &$teamPlayer) {
				if($teamPlayer->isDead())
					unset($realDefenders[$key]);
			} unset($teamPlayer);
		}
	}
}

function runAnAttack($realAttackers,$realDefenders) {
	global $template;
	$results = array('Attackers' => array('Traders' => array(), 'TotalDamage' => 0), 
					'Defenders' => array('Traders' => array(), 'TotalDamage' => 0));
	foreach($realAttackers as $accountID => &$teamPlayer) {
		$playerResults =& $teamPlayer->shootPlayers($realDefenders);
		$results['Attackers']['Traders'][] =& $playerResults;
		$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
	} unset($teamPlayer);
	foreach($realDefenders as $accountID => &$teamPlayer) {
		$playerResults =& $teamPlayer->shootPlayers($realAttackers);
		$results['Defenders']['Traders'][]  =& $playerResults;
		$results['Defenders']['TotalDamage'] += $playerResults['TotalDamage'];
	} unset($teamPlayer);
	$template->assignByRef('TraderCombatResults',$results);
}
?>