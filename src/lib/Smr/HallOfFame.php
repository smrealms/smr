<?php declare(strict_types=1);

namespace Smr;

use Page;
use SmrAccount;
use SmrGame;
use SmrPlayer;

/**
 * Collection of functions to help display the Hall of Fame tables.
 */
class HallOfFame {

	/**
	 * @param array<string> $allowedVis
	 * @return array<array<string, string>>
	 */
	public static function getHofCategories(array $allowedVis, ?int $game_id, int $account_id): array {
		// Get the HOF type that we're currently viewing
		$var = Session::getInstance()->getCurrentVar();
		if (isset($var['viewType'])) {
			$viewType = $var['viewType'];
			$viewTypeList = explode(':', $viewType);
		} else {
			$viewType = '';
			$viewTypeList = [];
		}

		$categories = [];
		$subcategories = [];
		foreach (SmrPlayer::getHOFVis() as $hofType => $hofVis) {
			if (!in_array($hofVis, $allowedVis)) {
				// Not allowed to view
				continue;
			}
			if (!str_starts_with($hofType, $viewType)) {
				// Isn't a subtype of the current type
				continue;
			}

			$typeList = explode(':', $hofType);
			$extra = array_values(array_diff($typeList, $viewTypeList));

			// Make each category a link to view the subcategory page
			$category = $extra[0];
			if (!isset($categories[$category])) {
				$container = Page::copy($var);
				$container['viewType'] = implode(':', array_merge($viewTypeList, [$category]));
				$categories[$category] = create_link($container, $category);

				// Prepare subcategories
				//$subcategories[$category] = [];
			}

			// Register all subcategories
			$subcategory = $extra[1] ?? 'View';
			if (!isset($subcategories[$category][$subcategory])) {
				$container = Page::copy($var);
				$rankMsg = '';
				if (count($extra) <= 2) {
					// Subcategory is a complete HOF type
					$rank = self::getHofRank($hofType, $account_id, $game_id);
					if ($rank['Rank'] != 0) {
						$rankMsg = ' (#' . $rank['Rank'] . ')';
					}
					$container['viewType'] = $hofType;
				} else {
					$container['viewType'] = implode(':', array_merge($viewTypeList, [$category, $subcategory]));
				}
				$subcategories[$category][$subcategory] = create_submit_link($container, $subcategory . $rankMsg);
			}
		}

		$output = [];
		foreach ($categories as $category => $link) {
			$output[] = [
				'link' => $link,
				'subcategories' => implode('&#32;', array_values($subcategories[$category])),
			];
		}
		return $output;
	}

	/**
	 * Conditionally hide displayed HoF stat.
	 *
	 * Hide the amount for:
	 * - alliance stats in live games for players not in your alliance
	 * - private stats for players who are not the current player
	 */
	public static function applyHofVisibilityMask(float $amount, string $vis, ?int $gameID, int $accountID): string|float {
		$session = Session::getInstance();
		$account = $session->getAccount();
		if (($vis == HOF_PRIVATE && $account->getAccountID() != $accountID) ||
		    ($vis == HOF_ALLIANCE && isset($gameID) &&
		     !SmrGame::getGame($gameID)->hasEnded() &&
		     !SmrPlayer::getPlayer($accountID, $gameID)->sameAlliance($session->getPlayer()))) {
			return '-';
		}
		return $amount;
	}

	public static function getHofRank(string $viewType, int $accountID, ?int $gameID): array {
		$db = Database::getInstance();
		// If no game specified, show total amount from completed games only
		$gameIDSql = ' AND game_id ' . (isset($gameID) ? '= ' . $db->escapeNumber($gameID) : 'IN (SELECT game_id FROM game WHERE end_time < ' . Epoch::time() . ' AND ignore_stats = ' . $db->escapeBoolean(false) . ')');

		$viewTypeList = explode(':', $viewType);
		$view = end($viewTypeList);

		$rank = ['Amount' => 0, 'Rank' => 0];
		if ($view == HOF_TYPE_DONATION) {
			$dbResult = $db->read('SELECT IFNULL(SUM(amount), 0) as amount FROM account_donated WHERE account_id=' . $db->escapeNumber($accountID));
		} elseif ($view == HOF_TYPE_USER_SCORE) {
			$statements = SmrAccount::getUserScoreCaseStatement($db);
			$dbResult = $db->read('SELECT ' . $statements['CASE'] . ' amount FROM (SELECT type, SUM(amount) amount FROM player_hof WHERE type IN (' . $statements['IN'] . ') AND account_id=' . $db->escapeNumber($accountID) . $gameIDSql . ' GROUP BY account_id,type) x');
		} else {
			$hofVis = SmrPlayer::getHOFVis();
			if (!isset($hofVis[$viewType])) {
				return $rank;
			}
			$dbResult = $db->read('SELECT IFNULL(SUM(amount), 0) amount FROM player_hof WHERE type=' . $db->escapeString($viewType) . ' AND account_id=' . $db->escapeNumber($accountID) . $gameIDSql);
		}

		$realAmount = $dbResult->record()->getFloat('amount');
		$vis = SmrPlayer::getHOFVis()[$viewType];
		$rank['Amount'] = self::applyHofVisibilityMask($realAmount, $vis, $gameID, $accountID);

		if ($view == HOF_TYPE_DONATION) {
			$dbResult = $db->read('SELECT COUNT(account_id) `rank` FROM (SELECT account_id FROM account_donated GROUP BY account_id HAVING SUM(amount)>' . $db->escapeNumber($rank['Amount']) . ') x');
		} elseif ($view == HOF_TYPE_USER_SCORE) {
			$statements = SmrAccount::getUserScoreCaseStatement($db);
			$dbResult = $db->read('SELECT COUNT(account_id) `rank` FROM (SELECT account_id FROM player_hof WHERE type IN (' . $statements['IN'] . ')' . $gameIDSql . ' GROUP BY account_id HAVING ' . $statements['CASE'] . '>' . $db->escapeNumber($rank['Amount']) . ') x');
		} else {
			$dbResult = $db->read('SELECT COUNT(account_id) `rank` FROM (SELECT account_id FROM player_hof WHERE type=' . $db->escapeString($viewType) . $gameIDSql . ' GROUP BY account_id HAVING SUM(amount)>' . $db->escapeNumber($realAmount) . ') x');
		}
		if ($dbResult->hasRecord()) {
			$rank['Rank'] = $dbResult->record()->getInt('rank') + 1;
		}
		return $rank;
	}

	public static function displayHOFRow(int $rank, int $accountID, float|string $amount): string {
		$var = Session::getInstance()->getCurrentVar();

		$account = Session::getInstance()->getAccount();
		if (isset($var['game_id']) && SmrGame::gameExists($var['game_id'])) {
			try {
				$hofPlayer = SmrPlayer::getPlayer($accountID, $var['game_id']);
			} catch (Exceptions\PlayerNotFound) {
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
		} elseif (isset($hofAccount) && is_object($hofAccount)) {
			$return .= ('<td ' . $bold . '>' . create_link($container, $hofAccount->getHofDisplayName()) . '</td>');
		} else {
			$return .= ('<td ' . $bold . '>Unknown</td>');
		}
		$return .= ('<td ' . $bold . '>' . $amount . '</td>');
		$return .= ('</tr>');
		return $return;
	}

	public static function buildBreadcrumb(Page $var, string $hofName): string {
		$container = Page::copy($var);
		unset($container['viewType']);
		$viewing = '<span class="bold">Currently viewing: </span>' . create_link($container, $hofName);

		if (isset($var['viewType'])) {
			$typeList = explode(':', $var['viewType']);
		} else {
			$typeList = [];
		}
		$breadcrumbTypeList = [];
		foreach ($typeList as $hofType) {
			$breadcrumbTypeList[] = $hofType;
			$container['viewType'] = implode(':', $breadcrumbTypeList);
			$viewing .= ' &rarr; ' . create_link($container, $hofType);
		}
		$viewing .= '<br /><br />';
		return $viewing;
	}

}
