<?php

function getHofRank($view,$viewType,$accountID,$gameID,&$db) {
	global $account,$player;
	$gameIDSql = ' AND game_id '.(isset($gameID) ? '= ' . $db->escapeNumber($gameID) : 'IN (SELECT game_id FROM game WHERE ignore_stats = '.$db->escapeBoolean(false).')');

	$vis = HOF_PUBLIC;
	$rank = array('Amount'=>0,'Rank'=>0);
	if($view == DONATION_NAME) {
		$db->query('SELECT SUM(amount) as amount FROM account_donated WHERE account_id='.$db->escapeNumber($accountID).' GROUP BY account_id LIMIT 1');
	}
	else if($view == USER_SCORE_NAME) {
		$statements = SmrAccount::getUserScoreCaseStatement($db);
		$query = 'SELECT '.$statements['CASE'].' amount FROM (SELECT type, SUM(amount) amount FROM player_hof WHERE type IN ('.$statements['IN'].') AND account_id='.$db->escapeNumber($accountID).$gameIDSql.' GROUP BY account_id,type) x ORDER BY amount DESC';
		$db->query($query);
	}
	else {
		$db->query('SELECT visibility FROM hof_visibility WHERE type='.$db->escapeArray($viewType,false,true,':',false).' LIMIT 1');
		if(!$db->nextRecord()) {
			return $rank;
		}
		$vis = $db->getField('visibility');
		$db->query('SELECT SUM(amount) amount FROM player_hof WHERE type='.$db->escapeArray($viewType,false,true,':',false) .' AND account_id='.$db->escapeNumber($accountID).$gameIDSql.' GROUP BY account_id LIMIT 1');
	}

	$realAmount = 0;
	if($db->nextRecord()) {
		if($db->getField('amount')!=null) {
			$realAmount = $db->getField('amount');
		}
	}
	if($vis==HOF_PUBLIC || $account->getAccountID()==$accountID) {
		$rank['Amount'] = $realAmount;
	}
	else if($vis==HOF_ALLIANCE&&$account->getAccountID()!=$accountID) {
		try {
			$hofPlayer =& SmrPlayer::getPlayer($accountID, $gameID);
			if($hofPlayer->sameAlliance($player)) {
				$rank['Amount'] = $realAmount;
			}
			else {
				$rank['Amount'] = '-'; //Show a - rather than 0 when hidden due to alliance restrictions so that it is clearer.
			}
		}
		catch(Exception $e) {
			$rank['Amount'] = '-';
		}
		if($rank['Amount'] == '-' && !isset($gameID)) {
			$db->query('SELECT SUM(amount) amount FROM player_hof WHERE type='.$db->escapeArray($viewType,false,true,':',false) .' AND account_id='.$accountID.' AND game_id IN (SELECT game_id FROM game WHERE end_date < '.TIME.' AND ignore_stats = '.$db->escapeBoolean(false).') GROUP BY account_id LIMIT 1');
			if($db->nextRecord()) {
				if($db->getField('amount')!=null) {
					$rank['Amount'] = $db->getField('amount');
				}
			}
		}
	}

	if($view == DONATION_NAME) {
		$db->query('SELECT COUNT(account_id) rank FROM (SELECT account_id FROM account_donated GROUP BY account_id HAVING SUM(amount)>' . $db->escapeNumber($rank['Amount']) . ') x');
	}
	else if($view == USER_SCORE_NAME) {
		$query = 'SELECT COUNT(account_id) rank FROM (SELECT account_id FROM player_hof WHERE type IN ('.$statements['IN'].')'.$gameIDSql.' GROUP BY account_id HAVING '.$statements['CASE'].'>' . $db->escapeNumber($rank['Amount']) . ') x';
		$db->query($query);
	}
	else {
		$db->query('SELECT COUNT(account_id) rank FROM (SELECT account_id FROM player_hof WHERE type='.$db->escapeArray($viewType,false,true,':',false).$gameIDSql.' GROUP BY account_id HAVING SUM(amount)>'.$db->escapeNumber($realAmount).') x');
	}
	if($db->nextRecord()) {
		$rank['Rank'] = $db->getInt('rank') + 1;
	}
	return $rank;
}

function displayHOFRow($rank,$accountID,$amount) {
	global $account,$player,$var;
	if(isset($var['game_id']) && Globals::isValidGame($var['game_id'])) {
		try {
			$hofPlayer =& SmrPlayer::getPlayer($accountID,$var['game_id']);
		}
		catch(Exception $e) {
			$hofAccount =& SmrAccount::getAccount($accountID);
		}
	}
	else {
		$hofAccount =& SmrAccount::getAccount($accountID);
	}
	$bold = '';
	if ($accountID == $account->getAccountID()) {
		$bold = ' class="bold"';
	}
	$return=('<tr>');
	$return.=('<td align="center"'.$bold.'>' . $rank . '</td>');

	$container = create_container('skeleton.php','hall_of_fame_player_detail.php');
	$container['account_id'] = $accountID;

	if (isset($var['game_id'])) {
		$container['game_id'] = $var['game_id'];
		$container['sending_page'] = 'personal_current_hof';
	}
	else {
		$container['sending_page'] = 'personal_hof';
	}
	if(isset($hofPlayer) && is_object($hofPlayer)) {
		$return.=('<td align="center"'.$bold.'>'.create_link($container, $hofPlayer->getPlayerName()) .'</td>');
	}
	else if(isset($hofAccount) && is_object($hofAccount)) {
		$return.=('<td align="center"'.$bold.'>'.create_link($container, $hofAccount->getHofName()) .'</td>');
	}
	else {
		$return.=('<td align="center"'.$bold.'>Unknown</td>');
	}
	$return.=('<td align="center"'.$bold.'>' . $amount . '</td>');
	$return.=('</tr>');
	return $return;
}

function buildBreadcrumb(&$var,&$hofTypes,$hofName) {
	$container = $var;
	unset($container['type']);
	unset($container['view']);
	$viewing= '<span class="bold">Currently viewing: </span>'.create_link($container,$hofName);
	$typeList = array();
	if(isset($var['type'])) {
		foreach($var['type'] as $type) {
			if(!is_array($hofTypes[$type])) {
				$var['type'] = $typeList;
				$var['view'] = $type;
				break;
			}
			else {
				$typeList[] = $type;
			}
			$viewing .= ' -&gt; ';
			$container = $var;
			$container['type'] = $typeList;
			unset($container['view']);
			$viewing.= create_link($container,$type);

			$hofTypes = $hofTypes[$type];
		}
	}
	if(isset($var['view'])) {
		$viewing .= ' -&gt; ';
		if(is_array($hofTypes[$var['view']])) {
			$typeList[] = $var['view'];
			$var['type'] = $typeList;
		}
		$container = $var;
		$viewing .= create_link($container,$var['view']);

		if(is_array($hofTypes[$var['view']])) {
			$hofTypes = $hofTypes[$var['view']];
			unset($var['view']);
		}
	}
	$viewing.= '<br /><br />';
	return $viewing;
}
?>