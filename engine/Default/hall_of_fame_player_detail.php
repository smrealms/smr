<?php
require_once(get_file_loc('hof.functions.inc'));

if (isset($var['account_id']))
	$account_id = $var['account_id'];
else
	$account_id = $account->getAccountID();
$game_id =null;
if (isset($var['game_id'])) $game_id = $var['game_id'];
$base = array();

if(isset($var['game_id'])) {
	try {
		$hofPlayer =& SmrPlayer::getPlayer($account_id,$var['game_id']);
	}
	catch(Exception $e) {
		create_error('That player has not yet joined this game.');
	}
	$template->assign('PageTopic',$hofPlayer->getPlayerName().'\'s Personal Hall of Fame For '.Globals::getGameName($var['game_id']));
}
else {
	$template->assign('PageTopic',$account->getHofName().'\'s All Time Personal Hall of Fame');
}
$PHP_OUTPUT.=('<div class="center">');
$allowedVisibities = array(HOF_PUBLIC);
if($account->getAccountID()==$account_id) {
	$allowedVisibities[] = HOF_ALLIANCE;
	$allowedVisibities[] = HOF_PRIVATE;
}
else if(isset($hofPlayer) && $hofPlayer->sameAlliance($player)) {
	$allowedVisibities[] = HOF_ALLIANCE;
}
$db->query('SELECT type FROM hof_visibility WHERE visibility IN (' . $db->escapeArray($allowedVisibities) . ') ORDER BY type');
//$db->query('SELECT DISTINCT type FROM player_hof JOIN hof_visibility USING(type) WHERE visibility IN (' . $db->escapeArray($allowedVisibities) . ') AND account_id='.$account_id . (isset($var['game_id']) ? ' AND game_id='.$var['game_id'] : '').' ORDER BY type');
define('DONATION_NAME','Money Donated To SMR');
define('USER_SCORE_NAME','User Score');
$hofTypes = array(DONATION_NAME=>true, USER_SCORE_NAME=>true);
while($db->nextRecord()) {
	$hof =& $hofTypes;
	$typeList = explode(':',$db->getField('type'));
	foreach($typeList as $type) {
		if(!isset($hof[$type])) {
			$hof[$type] = array();
		}
		$hof =& $hof[$type];
	}
	$hof = true;
}
$PHP_OUTPUT .= buildBreadcrumb($var,$hofTypes,'Personal HoF');
$PHP_OUTPUT.= '<table class="standard" align="center">';

if(!isset($var['view'])) {
	$PHP_OUTPUT.=('<tr><th>Category</th><th width="60%">Subcategory</th></tr>');
	
	foreach($hofTypes as $type => $value) {
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td>'.$type.'</td>');
		$container = $var;
		if (!isset($var['type']))
			$container['type'] = array();
		$container['type'][] = $type;
		$PHP_OUTPUT.=('<td valign="middle">');
		$i=0;
		if(is_array($value)) {
			foreach($value as $subType => $subTypeValue) {
				++$i;
				$container['view'] = $subType;
				
				$rankType = $container['type'];
				$rankType[] = $subType;
				$rank = getHofRank($subType,$rankType,$account->getAccountID(),$game_id,$db);
				$rankMsg='';
				if($rank['Rank']!=0)
					$rankMsg = ' (#' . $rank['Rank'] .')';
				
				$PHP_OUTPUT.=create_submit_link($container,$subType.$rankMsg);
				$PHP_OUTPUT.=('&nbsp;');
				if ($i % 3 == 0) $PHP_OUTPUT.=('<br />');
			}
		}
		else {
			unset($container['view']);
			$rank = getHofRank($type,$container['type'],$account->getAccountID(),$game_id,$db);
			$PHP_OUTPUT.=create_submit_link($container,'View (#' . $rank['Rank'] .')');
		}
		$PHP_OUTPUT.=('</td></tr>');
	}
}
else {
	$PHP_OUTPUT.=('<tr><th>Rank</th><th>Player</th><th>Total</th></tr>');
	
	$viewType = $var['type'];
	$viewType[] = $var['view'];

	$hofRank = getHofRank($var['view'],$viewType,$account_id,$game_id,$db);
	
	if($account->getAccountID() != $account_id) {
		//current player's score.
		$playerRank = getHofRank($var['view'],$viewType,$account->getAccountID(),$game_id,$db);
		
		//display in order
		if($playerRank['Rank']<$hofRank)
			$PHP_OUTPUT .= displayHOFRow($playerRank['Rank'],$account->getAccountID(),$playerRank['Amount']);
		else
			$PHP_OUTPUT .= displayHOFRow($hofRank['Rank'],$account_id,$hofRank['Amount']);
		
		if($playerRank['Rank']>$hofRank)
			$PHP_OUTPUT .= displayHOFRow($playerRank['Rank'],$account->getAccountID(),$playerRank['Amount']);
		else
			$PHP_OUTPUT .= displayHOFRow($hofRank['Rank'],$account_id,$hofRank['Amount']);
	}
	else
		$PHP_OUTPUT .= displayHOFRow($hofRank['Rank'],$account_id,$hofRank['Amount']);
}

$PHP_OUTPUT.=('</table></div>');

?>