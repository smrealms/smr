<?php
require_once(get_file_loc('hof.functions.inc'));
$game_id = null;
if (isset($var['game_id'])) $game_id = $var['game_id'];

if (empty($game_id)) {
	$topic = 'All Time Hall of Fame';
}
else {
	$topic = Globals::getGameName($game_id).' Hall of Fame';
}
$template->assign('PageTopic',$topic);

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'hall_of_fame_player_detail.php';
if (isset($game_id)) $container['game_id'] = $game_id;
$template->assign('PersonalHofHREF', SmrSession::getNewHREF($container));

$db->query('SELECT type FROM hof_visibility WHERE visibility != '. $db->escapeString(HOF_PRIVATE) . ' ORDER BY type');
const DONATION_NAME = 'Money Donated To SMR';
const USER_SCORE_NAME = 'User Score';
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
$template->assign('Breadcrumb', buildBreadcrumb($var,$hofTypes,isset($game_id)?'Current HoF':'Global HoF'));

if(!isset($var['view'])) {
	$categories = getHofCategories($hofTypes, $game_id, $account->getAccountID());
	$template->assign('Categories', $categories);
}
else {
	$gameIDSql = ' AND game_id '.(isset($game_id) ? '= ' . $db->escapeNumber($game_id) : 'IN (SELECT game_id FROM game WHERE ignore_stats = '.$db->escapeBoolean(false).')');

	$vis = HOF_PUBLIC;
	$rank=1;
	$foundMe=false;
	$viewType = $var['type'];
	$viewType[] = $var['view'];
	if($var['view'] == DONATION_NAME)
		$db->query('SELECT account_id, SUM(amount) as amount FROM account_donated
					GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25');
	else if($var['view'] == USER_SCORE_NAME) {
		$statements = SmrAccount::getUserScoreCaseStatement($db);
		$query = 'SELECT account_id, '.$statements['CASE'].' amount FROM (SELECT account_id, type, SUM(amount) amount FROM player_hof WHERE type IN ('.$statements['IN'].')'.$gameIDSql.' GROUP BY account_id,type) x GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25';
		$db->query($query);
	}
	else {
		$db->query('SELECT visibility FROM hof_visibility WHERE type = '.$db->escapeArray($viewType,false,true,':',false).' LIMIT 1');
		if($db->nextRecord()) {
			$vis = $db->getField('visibility');
		}
		$db->query('SELECT account_id,SUM(amount) amount FROM player_hof WHERE type='.$db->escapeArray($viewType,false,true,':',false).$gameIDSql.' GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25');
	}
	$rows = [];
	while($db->nextRecord()) {
		$accountID = $db->getField('account_id');
		if($accountID == $account->getAccountID()) {
			$foundMe = true;
			$amount = $db->getField('amount');
		}
		else if($vis==HOF_PUBLIC) {
			$amount = $db->getField('amount');
		}
		else if($vis==HOF_ALLIANCE) {
			$rankInfo = getHofRank($var['view'], $viewType, $db->getField('account_id'), $game_id);
			$amount = $rankInfo['Amount'];
		}
		else {
			$amount = '-';
		}
		$rows[] = displayHOFRow($rank++, $accountID, $amount);
	}
	if(!$foundMe) {
		$rank = getHofRank($var['view'],$viewType,$account->getAccountID(),$game_id);
		$rows[] = displayHOFRow($rank['Rank'], $account->getAccountID(), $rank['Amount']);
	}
	$template->assign('Rows', $rows);
}
