<?php

$template->assign('PageTopic','Extended User Rankings');
require_once(get_file_loc('menu.inc'));
if (SmrSession::$game_id != 0)
	create_trader_menu();

$PHP_OUTPUT.=('You have a score of <span class="red">'.number_format($account->getScore()).'</span>.<br /><br />');
$PHP_OUTPUT.=('You are ranked as a <font size="4" color="greenyellow">'.$account->getRankName().'</font> player.<p><br />');
foreach (Globals::getUserRanking() as $rankID => $rankName) {
	$PHP_OUTPUT.= $rankName . ' - ';
	$PHP_OUTPUT.= ceil(pow((max(0,$rankID-1))*SmrAccount::USER_RANKINGS_RANK_BOUNDARY,1/SmrAccount::USER_RANKINGS_TOTAL_SCORE_POW)) . ' points.';
	$PHP_OUTPUT.=('<br />');
}
$PHP_OUTPUT.=('<br />');
$individualScores = $account->getIndividualScores();
$PHP_OUTPUT.=('<b>Extended Scores</b><br />');
foreach($individualScores as $statScore) {
	$first=true;
	foreach($statScore['Stat'] as $stat) {
		if($first)
			$first=false;
		else
			$PHP_OUTPUT.=' - ';
		$PHP_OUTPUT.=$stat;
	}
	$PHP_OUTPUT.=(', has a stat of '.number_format($account->getHOF($statScore['Stat'])).' and a score of ' . number_format(round($statScore['Score'])).' (roughly)<br />');
}

if (SmrSession::$game_id != 0) {
	//current game stats
	$PHP_OUTPUT.=('<br /><br />');
	$PHP_OUTPUT.=('<b>Current Game Extended Stats</b><br />');
	$individualScores = $account->getIndividualScores($player);
	foreach($individualScores as $statScore) {
		$first=true;
		foreach($statScore['Stat'] as $stat) {
			if($first)
				$first=false;
			else
				$PHP_OUTPUT.=' - ';
			$PHP_OUTPUT.=$stat;
		}
		$PHP_OUTPUT.=(', has a stat of '.number_format($player->getHOF($statScore['Stat'])).' and a score of ' . number_format(round($statScore['Score'])).' (roughly)<br />');
	}
}
$PHP_OUTPUT.='<br /><br />Note: The total score will be lower than the sum of the individual scores as the points you get for each action is reduced as you do it more (people who are good at all parts of the game get more points than someone who is only good at one part).';
