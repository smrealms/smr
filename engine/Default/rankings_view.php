<?php

$rank_id = $account->get_rank();

$template->assign('PageTopic','Extended User Rankings');
include(get_file_loc('menue.inc'));
if (SmrSession::$game_id != 0)
	$PHP_OUTPUT.=create_trader_menue();

$db->query('SELECT * FROM rankings WHERE rankings_id = '.$rank_id);
if ($db->nextRecord())
	$rank_name = $db->getField('rankings_name');

// initialize vars
$kills = 0;
$exp = 0;

// get stats
$db->query('SELECT * from account_has_stats WHERE account_id = '.SmrSession::$account_id);
if ($db->nextRecord()) {

	$kills = ($db->getField('kills') > 0) ? $db->getField('kills') : 0;
	$exp = ($db->getField('experience_traded') > 0) ? $db->getField('experience_traded') : 0;

}

$PHP_OUTPUT.=('You have a score of <font color="red">'.number_format($account->getScore()).'</font>.<br /><br />');
$PHP_OUTPUT.=('You are ranked as a <font size="4" color="greenyellow">'.$account->get_rank_name().'</font> player.<p><br />');
$db->query('SELECT * FROM user_rankings ORDER BY rank');
$i = 0;
while ($db->nextRecord())
{
    if ($i > 0)
    	$PHP_OUTPUT.=('<br />');
    $PHP_OUTPUT.= $db->getField('rank_name') . ' - ';
    $PHP_OUTPUT.= ceil(pow($db->getField('rank'),1/SmrAccount::USER_RANKINGS_TOTAL_SCORE_POW)*SmrAccount::USER_RANKINGS_RANK_BOUNDARY) . ' points.';
	$i++;
}
$db2 = new SmrMySqlDatabase();
$PHP_OUTPUT.=('<br /><br />');
$individualScores =& $account->getIndividualScores();
$PHP_OUTPUT.=('<b>Extended Scores</b><br />');
foreach($individualScores as $statScore)
{
	$first=true;
	foreach($statScore['Stat'] as $stat)
	{
		if($first)
			$first=false;
		else
			$PHP_OUTPUT.=' - ';
		$PHP_OUTPUT.=$stat;
	}
	$PHP_OUTPUT.=(', has a stat of '.$account->getHOF($statScore['Stat']).' and a score of ' . number_format(round(pow($statScore['Score'],SmrAccount::USER_RANKINGS_TOTAL_SCORE_POW))).' (roughly)<br />');
}

if (SmrSession::$game_id != 0)
{
	//current game stats
	$PHP_OUTPUT.=('<br /><br />');
	$PHP_OUTPUT.=('<b>Current Game Extended Stats</b><br />');
	$individualScores =& $account->getIndividualScores($player);
	foreach($individualScores as $statScore)
	{
		$first=true;
		foreach($statScore['Stat'] as $stat)
		{
			if($first)
				$first=false;
			else
				$PHP_OUTPUT.=' - ';
			$PHP_OUTPUT.=$stat;
		}
		$PHP_OUTPUT.=(', has a stat of '.$player->getHOF($statScore['Stat']).' and a score of ' . number_format(round(pow($statScore['Score'],SmrAccount::USER_RANKINGS_TOTAL_SCORE_POW))).' (roughly)<br />');
	}
}
?>
