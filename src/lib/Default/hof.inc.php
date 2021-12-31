<?php declare(strict_types=1);

function getHofCategories(array $hofTypes, ?int $game_id, int $account_id) : array {
	$var = Smr\Session::getInstance()->getCurrentVar();
	$categories = [];
	foreach ($hofTypes as $type => $value) {
		// Make each category a link to view the subcategory page
		$container = Page::copy($var);
		$container['view'] = $type;
		if (!isset($var['type'])) {
			$container['type'] = array();
		}
		$link = create_link($container, $type);

		// Make the subcategory buttons
		$container = Page::copy($var);
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
					$rankMsg = ' (#' . $rank['Rank'] . ')';
				}
				$subcategories[] = create_submit_link($container, $subType . $rankMsg);
			}
		} else {
			$rank = getHofRank($type, $container['type'], $account_id, $game_id);
			$subcategories[] = create_submit_link($container, 'View (#' . $rank['Rank'] . ')');
		}

		$categories[] = [
			'link' => $link,
			'subcategories' => join('&#32;', $subcategories),
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
function applyHofVisibilityMask(float $amount, string $vis, ?int $gameID, int $accountID) : string|float {
	$session = Smr\Session::getInstance();
	$account = $session->getAccount();
	if (($vis == HOF_PRIVATE && $account->getAccountID() != $accountID) ||
	    ($vis == HOF_ALLIANCE && isset($gameID) &&
	     !SmrGame::getGame($gameID)->hasEnded() &&
	     !SmrPlayer::getPlayer($accountID, $gameID)->sameAlliance($session->getPlayer())))
	{
		return '-';
	} else {
		return $amount;
	}
}

function getHofRank(string $view, array $viewType, int $accountID, ?int $gameID) : array {
	$db = Smr\Database::getInstance();
	// If no game specified, show total amount from completed games only
	$gameIDSql = ' AND game_id ' . (isset($gameID) ? '= ' . $db->escapeNumber($gameID) : 'IN (SELECT game_id FROM game WHERE end_time < ' . Smr\Epoch::time() . ' AND ignore_stats = ' . $db->escapeBoolean(false) . ')');

	$vis = HOF_PUBLIC;
	$rank = array('Amount'=>0, 'Rank'=>0);
	if ($view == DONATION_NAME) {
		$dbResult = $db->read('SELECT SUM(amount) as amount FROM account_donated WHERE account_id=' . $db->escapeNumber($accountID) . ' GROUP BY account_id LIMIT 1');
	} else if ($view == USER_SCORE_NAME) {
		$statements = SmrAccount::getUserScoreCaseStatement($db);
		$dbResult = $db->read('SELECT ' . $statements['CASE'] . ' amount FROM (SELECT type, SUM(amount) amount FROM player_hof WHERE type IN (' . $statements['IN'] . ') AND account_id=' . $db->escapeNumber($accountID) . $gameIDSql . ' GROUP BY account_id,type) x ORDER BY amount DESC');
	} else {
		$dbResult = $db->read('SELECT visibility FROM hof_visibility WHERE type=' . $db->escapeArray($viewType, ':', false) . ' LIMIT 1');
		if (!$dbResult->hasRecord()) {
			return $rank;
		}
		$vis = $dbResult->record()->getString('visibility');
		$dbResult = $db->read('SELECT SUM(amount) amount FROM player_hof WHERE type=' . $db->escapeArray($viewType, ':', false) . ' AND account_id=' . $db->escapeNumber($accountID) . $gameIDSql . ' GROUP BY account_id LIMIT 1');
	}

	$realAmount = 0;
	if ($dbResult->hasRecord()) {
		$realAmount = $dbResult->record()->getFloat('amount');
	}
	$rank['Amount'] = applyHofVisibilityMask($realAmount, $vis, $gameID, $accountID);

	if ($view == DONATION_NAME) {
		$dbResult = $db->read('SELECT COUNT(account_id) `rank` FROM (SELECT account_id FROM account_donated GROUP BY account_id HAVING SUM(amount)>' . $db->escapeNumber($rank['Amount']) . ') x');
	} else if ($view == USER_SCORE_NAME) {
		$dbResult = $db->read('SELECT COUNT(account_id) `rank` FROM (SELECT account_id FROM player_hof WHERE type IN (' . $statements['IN'] . ')' . $gameIDSql . ' GROUP BY account_id HAVING ' . $statements['CASE'] . '>' . $db->escapeNumber($rank['Amount']) . ') x');
	} else {
		$dbResult = $db->read('SELECT COUNT(account_id) `rank` FROM (SELECT account_id FROM player_hof WHERE type=' . $db->escapeArray($viewType, ':', false) . $gameIDSql . ' GROUP BY account_id HAVING SUM(amount)>' . $db->escapeNumber($realAmount) . ') x');
	}
	if ($dbResult->hasRecord()) {
		$rank['Rank'] = $dbResult->record()->getInt('rank') + 1;
	}
	return $rank;
}

function displayHOFRow(int $rank, int $accountID, float|string $amount) : string {
	$var = Smr\Session::getInstance()->getCurrentVar();

	$account = Smr\Session::getInstance()->getAccount();
	if (isset($var['game_id']) && Globals::isValidGame($var['game_id'])) {
		try {
			$hofPlayer = SmrPlayer::getPlayer($accountID, $var['game_id']);
		} catch (Smr\Exceptions\PlayerNotFound) {
			$hofAccount = SmrAccount::getAccount($accountID);
		}
	} else {
		$hofAccount = SmrAccount::getAccount($accountID);
	}
	$bold = '';
	if ($accountID == $account->getAccountID()) {
		$bold = 'class="bold"';
	}
	$return = ('<tr>');
	$return .= ('<td ' . $bold . '>' . $rank . '</td>');

	$container = Page::create('skeleton.php', 'hall_of_fame_player_detail.php');
	$container['account_id'] = $accountID;

	if (isset($var['game_id'])) {
		$container->addVar('game_id');
	}

	if (isset($hofPlayer) && is_object($hofPlayer)) {
		$return .= ('<td ' . $bold . '>' . create_link($container, htmlentities($hofPlayer->getPlayerName())) . '</td>');
	} else if (isset($hofAccount) && is_object($hofAccount)) {
		$return .= ('<td ' . $bold . '>' . create_link($container, $hofAccount->getHofDisplayName()) . '</td>');
	} else {
		$return .= ('<td ' . $bold . '>Unknown</td>');
	}
	$return .= ('<td ' . $bold . '>' . $amount . '</td>');
	$return .= ('</tr>');
	return $return;
}

function buildBreadcrumb(Page $var, array &$hofTypes, string $hofName) : string {
	$container = Page::copy($var);
	if (isset($container['type'])) {
		unset($container['type']);
	}
	if (isset($container['view'])) {
		unset($container['view']);
	}
	$viewing = '<span class="bold">Currently viewing: </span>' . create_link($container, $hofName);
	$typeList = array();
	if (isset($var['type'])) {
		foreach ($var['type'] as $type) {
			if (!is_array($hofTypes[$type])) {
				$var['type'] = $typeList;
				$var['view'] = $type;
				break;
			} else {
				$typeList[] = $type;
			}
			$viewing .= ' &rarr; ';
			$container = Page::copy($var);
			$container['type'] = $typeList;
			if (isset($container['view'])) {
				unset($container['view']);
			}
			$viewing .= create_link($container, $type);

			$hofTypes = $hofTypes[$type];
		}
	}
	if (isset($var['view'])) {
		$viewing .= ' &rarr; ';
		if (is_array($hofTypes[$var['view']])) {
			$typeList[] = $var['view'];
			$var['type'] = $typeList;
		}
		$container = Page::copy($var);
		$viewing .= create_link($container, $var['view']);

		if (is_array($hofTypes[$var['view']])) {
			$hofTypes = $hofTypes[$var['view']];
			unset($var['view']);
		}
	}
	$viewing .= '<br /><br />';
	return $viewing;
}
