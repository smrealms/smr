<?php

function getHofCategories($hofTypes, $game_id, $account_id) {
	global $var;
	$categories = [];
	foreach ($hofTypes as $type => $value) {
		// Make each category a link to view the subcategory page
		$container = $var;
		$container['view'] = $type;
		if (!isset($var['type'])) {
			$container['type'] = array();
		}
		$link = create_link($container, $type);

		// Make the subcategory buttons
		$container = $var;
		if (!isset($var['type'])) {
			$container['type'] = array();
		}
		$container['type'][] = $type;
		$subcategories = [];
		if (is_array($value)) {
			foreach ($value as $subType => $subTypeValue) {
				$container['view'] = $subType;
				$rankType = $container['type'];
				$rankType[] = $subType;
				$rank = getHofRank($subType, $rankType, $account_id, $game_id);
				$rankMsg = '';
				if ($rank['Rank'] != 0) {
					$rankMsg = ' (#' . $rank['Rank'] .')';
				}
				$subcategories[] = create_submit_link($container, $subType.$rankMsg);
			}
		} else {
			unset($container['view']);
			$rank = getHofRank($type, $container['type'], $account_id, $game_id);
			$subcategories[] = create_submit_link($container, 'View (#' . $rank['Rank'] .')');
		}

		$categories[] = [
			'link' => $link,
			'subcategories' => join('&nbsp;', $subcategories),
		];
	}
	return $categories;
}

/**
 * Conditionally hide displayed HoF stat.
 *
 * Hide the amount for:
 * - alliance stats in live games for players not in your alliance
 * - private stats for players who are not the current player
 */
function applyHofVisibilityMask($amount, $vis, $gameID, $accountID) {
	global $account, $player;
	if (($vis == HOF_PRIVATE && $account->getAccountID() != $accountID) ||
	    ($vis == HOF_ALLIANCE && isset($gameID) &&
	     !SmrGame::getGame($gameID)->hasEnded() &&
	     !SmrPlayer::getPlayer($accountID, $gameID)->sameAlliance($player)))
	{
		return '-';
	} else {
		return $amount;
	}
}

function getHofRank($view,$viewType,$accountID,$gameID) {
	global $account;
	$db = new SmrMySqlDatabase();
	// If no game specified, show total amount from completed games only
	$gameIDSql = ' AND game_id '.(isset($gameID) ? '= ' . $db->escapeNumber($gameID) : 'IN (SELECT game_id FROM game WHERE end_time < '.TIME.' AND ignore_stats = '.$db->escapeBoolean(false).')');

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
	$rank['Amount'] = applyHofVisibilityMask($realAmount, $vis, $gameID, $accountID);

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
			$hofPlayer = SmrPlayer::getPlayer($accountID,$var['game_id']);
		}
		catch(PlayerNotFoundException $e) {
			$hofAccount = SmrAccount::getAccount($accountID);
		}
	}
	else {
		$hofAccount = SmrAccount::getAccount($accountID);
	}
	$bold = '';
	if ($accountID == $account->getAccountID()) {
		$bold = 'class="bold"';
	}
	$return=('<tr>');
	$return.=('<td '.$bold.'>' . $rank . '</td>');

	$container = create_container('skeleton.php','hall_of_fame_player_detail.php');
	$container['account_id'] = $accountID;

	if (isset($var['game_id'])) {
		$container['game_id'] = $var['game_id'];
	}

	if(isset($hofPlayer) && is_object($hofPlayer)) {
		$return.=('<td '.$bold.'>'.create_link($container, $hofPlayer->getPlayerName()) .'</td>');
	}
	else if(isset($hofAccount) && is_object($hofAccount)) {
		$return.=('<td '.$bold.'>'.create_link($container, $hofAccount->getHofName()) .'</td>');
	}
	else {
		$return.=('<td '.$bold.'>Unknown</td>');
	}
	$return.=('<td '.$bold.'>' . $amount . '</td>');
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
			$viewing .= ' &rarr; ';
			$container = $var;
			$container['type'] = $typeList;
			unset($container['view']);
			$viewing.= create_link($container,$type);

			$hofTypes = $hofTypes[$type];
		}
	}
	if(isset($var['view'])) {
		$viewing .= ' &rarr; ';
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
