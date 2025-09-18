<?php declare(strict_types=1);

namespace Smr;

use Smr\Exceptions\PlayerNotFound;
use Smr\Pages\Account\HallOfFameAll;
use Smr\Pages\Account\HallOfFamePersonal;

/**
 * Collection of functions to help display the Hall of Fame tables.
 */
class HallOfFame {

	/**
	 * @param array<string> $allowedVis
	 * @return array<array<string, string>>
	 */
	public static function getHofCategories(HallOfFameAll|HallOfFamePersonal $page, array $allowedVis, ?int $game_id, int $account_id): array {
		// Get the HOF type that we're currently viewing
		if ($page->viewType !== null) {
			$viewTypeFilter = $page->viewType . ':'; // avoid matching partial types
			$viewTypeList = explode(':', $page->viewType);
		} else {
			$viewTypeFilter = '';
			$viewTypeList = [];
		}

		$categories = [];
		$subcategories = [];
		foreach (Player::getHOFVis() as $hofType => $hofVis) {
			if (!in_array($hofVis, $allowedVis, true)) {
				// Not allowed to view
				continue;
			}
			if (!str_starts_with($hofType, $viewTypeFilter)) {
				// Isn't a subtype of the current type
				continue;
			}

			$typeList = explode(':', $hofType);
			$extra = array_values(array_diff($typeList, $viewTypeList));

			// Make each category a link to view the subcategory page
			$category = $extra[0];
			if (!isset($categories[$category])) {
				$containerViewType = implode(':', array_merge($viewTypeList, [$category]));
				$container = $page->withViewType($containerViewType);
				$categories[$category] = create_link($container, $category);

				// Prepare subcategories
				//$subcategories[$category] = [];
			}

			// Register all subcategories
			$subcategory = $extra[1] ?? 'View';
			if (!isset($subcategories[$category][$subcategory])) {
				$rankMsg = '';
				if (count($extra) <= 2) {
					// Subcategory is a complete HOF type
					$rank = self::getHofRank($hofType, $account_id, $game_id);
					if ($rank['Rank'] !== 0) {
						$rankMsg = ' (#' . $rank['Rank'] . ')';
					}
					$containerViewType = $hofType;
				} else {
					$containerViewType = implode(':', array_merge($viewTypeList, [$category, $subcategory]));
				}
				$container = $page->withViewType($containerViewType);
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
		if (
			($vis === HOF_PRIVATE && $account->getAccountID() !== $accountID) ||
			(
				$vis === HOF_ALLIANCE &&
				isset($gameID) &&
				!Game::getGame($gameID)->hasEnded() &&
				!Player::getPlayer($accountID, $gameID)->sameAlliance($session->getPlayer())
			)
		) {
			return '-';
		}
		return $amount;
	}

	/**
	 * @return array{Rank: int, Amount: float|string}
	 */
	public static function getHofRank(string $viewType, int $accountID, ?int $gameID): array {
		$db = Database::getInstance();
		// If no game specified, show total amount from completed games only
		$gameIDSql = ' AND IF(:game_id IS NULL, game_id IN (SELECT game_id FROM game WHERE end_time < :now AND ignore_stats = \'FALSE\'), game_id = :game_id)';
		$gameIDParams = [
			'game_id' => $gameID,
			'now' => Epoch::time(),
		];

		$rank = ['Amount' => 0, 'Rank' => 0];
		if ($viewType === HOF_TYPE_DONATION) {
			$dbResult = $db->read('SELECT IFNULL(SUM(amount), 0) as amount FROM account_donated WHERE account_id = :account_id', [
				'account_id' => $db->escapeNumber($accountID),
			]);
		} elseif ($viewType === HOF_TYPE_USER_SCORE) {
			$statements = Account::getUserScoreCaseStatement();
			$dbResult = $db->read('SELECT ' . $statements['CASE'] . ' amount FROM (SELECT type, SUM(amount) amount FROM player_hof WHERE type IN (:hof_types) AND account_id = :account_id' . $gameIDSql . ' GROUP BY account_id,type) x', [
				'hof_types' => $db->escapeArray($statements['IN']),
				'account_id' => $db->escapeNumber($accountID),
				...$gameIDParams,
			]);
		} else {
			$hofVis = Player::getHOFVis();
			if (!isset($hofVis[$viewType])) {
				return $rank;
			}
			$dbResult = $db->read('SELECT IFNULL(SUM(amount), 0) amount FROM player_hof WHERE type = :hof_type AND account_id = :account_id' . $gameIDSql, [
				'account_id' => $db->escapeNumber($accountID),
				'hof_type' => $db->escapeString($viewType),
				...$gameIDParams,
			]);
		}

		$realAmount = $dbResult->record()->getFloat('amount');
		$vis = Player::getHOFVis()[$viewType];
		$rank['Amount'] = self::applyHofVisibilityMask($realAmount, $vis, $gameID, $accountID);

		if ($viewType === HOF_TYPE_DONATION) {
			$dbResult = $db->read('SELECT COUNT(account_id) `rank` FROM (SELECT account_id FROM account_donated GROUP BY account_id HAVING SUM(amount) > :amount) x', [
				'amount' => $db->escapeNumber($realAmount),
			]);
		} elseif ($viewType === HOF_TYPE_USER_SCORE) {
			$statements = Account::getUserScoreCaseStatement();
			$dbResult = $db->read('SELECT COUNT(account_id) `rank` FROM (SELECT account_id FROM player_hof WHERE type IN (:hof_types)' . $gameIDSql . ' GROUP BY account_id HAVING ' . $statements['CASE'] . ' > :amount) x', [
				'hof_types' => $db->escapeArray($statements['IN']),
				'amount' => $db->escapeNumber($realAmount),
				...$gameIDParams,
			]);
		} else {
			$dbResult = $db->read('SELECT COUNT(account_id) `rank` FROM (SELECT account_id FROM player_hof WHERE type = :hof_type' . $gameIDSql . ' GROUP BY account_id HAVING SUM(amount) > :amount) x', [
				'amount' => $db->escapeNumber($realAmount),
				'hof_type' => $db->escapeString($viewType),
				...$gameIDParams,
			]);
		}
		if ($dbResult->hasRecord()) {
			$rank['Rank'] = $dbResult->record()->getInt('rank') + 1;
		}
		return $rank;
	}

	public static function displayHOFRow(int $rank, int $accountID, ?int $gameID, float|string $amount): string {
		$account = Session::getInstance()->getAccount();
		if ($gameID !== null && Game::gameExists($gameID)) {
			try {
				$hofPlayer = Player::getPlayer($accountID, $gameID);
			} catch (PlayerNotFound) {
				$hofAccount = Account::getAccount($accountID);
			}
		} else {
			$hofAccount = Account::getAccount($accountID);
		}
		$bold = '';
		if ($accountID === $account->getAccountID()) {
			$bold = 'class="bold"';
		}
		$return = ('<tr>');
		$return .= ('<td ' . $bold . '>' . $rank . '</td>');

		$container = new HallOfFamePersonal($accountID, $gameID);

		if (isset($hofPlayer)) {
			$return .= ('<td ' . $bold . '>' . create_link($container, htmlentities($hofPlayer->getPlayerName())) . '</td>');
		} elseif (isset($hofAccount)) {
			$return .= ('<td ' . $bold . '>' . create_link($container, $hofAccount->getHofDisplayName()) . '</td>');
		} else {
			$return .= ('<td ' . $bold . '>Unknown</td>');
		}
		$return .= ('<td ' . $bold . '>' . $amount . '</td>');
		$return .= ('</tr>');
		return $return;
	}

	public static function buildBreadcrumb(HallOfFameAll|HallOfFamePersonal $page, string $hofName): string {
		$container = $page->withViewType(null);
		$viewing = '<span class="bold">Currently viewing: </span>' . create_link($container, $hofName);

		if ($page->viewType !== null) {
			$typeList = explode(':', $page->viewType);
		} else {
			$typeList = [];
		}
		$breadcrumbTypeList = [];
		foreach ($typeList as $hofType) {
			$breadcrumbTypeList[] = $hofType;
			$viewType = implode(':', $breadcrumbTypeList);
			$container = $page->withViewType($viewType);
			$viewing .= ' &rarr; ' . create_link($container, $hofType);
		}
		$viewing .= '<br /><br />';
		return $viewing;
	}

}
