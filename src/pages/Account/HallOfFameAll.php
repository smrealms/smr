<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Database;
use Smr\Epoch;
use Smr\HallOfFame;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAccount;
use SmrGame;
use SmrPlayer;

class HallOfFameAll extends AccountPage {

	use ReusableTrait;

	public string $file = 'hall_of_fame_new.php';

	public function __construct(
		private readonly ?int $gameID = null,
		public readonly ?string $viewType = null
	) {}

	/**
	 * Construct a new object with the same properties, but a different
	 * viewType.
	 */
	public function withViewType(?string $viewType): self {
		return new self($this->gameID, $viewType);
	}

	public function build(SmrAccount $account, Template $template): void {
		$game_id = $this->gameID;

		if (empty($game_id)) {
			$topic = 'All Time Hall of Fame';
		} else {
			$topic = 'Hall of Fame: ' . SmrGame::getGame($game_id)->getDisplayName();
		}
		$template->assign('PageTopic', $topic);

		$container = new HallOfFamePersonal($account->getAccountID(), $game_id);
		$template->assign('PersonalHofHREF', $container->href());

		$breadcrumb = HallOfFame::buildBreadcrumb($this, isset($game_id) ? 'Current HoF' : 'Global HoF');
		$template->assign('Breadcrumb', $breadcrumb);

		$viewType = $this->viewType;
		$hofVis = SmrPlayer::getHOFVis();

		if (!isset($hofVis[$viewType])) {
			// Not a complete HOF type, so continue to show categories
			$allowedVis = [HOF_PUBLIC, HOF_ALLIANCE];
			$categories = HallOfFame::getHofCategories($this, $allowedVis, $game_id, $account->getAccountID());
			$template->assign('Categories', $categories);

		} else {
			// Rankings page
			$db = Database::getInstance();
			$gameIDSql = ' AND game_id ' . (isset($game_id) ? '= ' . $db->escapeNumber($game_id) : 'IN (SELECT game_id FROM game WHERE end_time < ' . Epoch::time() . ' AND ignore_stats = ' . $db->escapeBoolean(false) . ')');

			$rank = 1;
			$foundMe = false;

			if ($viewType == HOF_TYPE_DONATION) {
				$dbResult = $db->read('SELECT account_id, SUM(amount) as amount FROM account_donated
							GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25');
			} elseif ($viewType == HOF_TYPE_USER_SCORE) {
				$statements = SmrAccount::getUserScoreCaseStatement($db);
				$query = 'SELECT account_id, ' . $statements['CASE'] . ' amount FROM (SELECT account_id, type, SUM(amount) amount FROM player_hof WHERE type IN (' . $statements['IN'] . ')' . $gameIDSql . ' GROUP BY account_id,type) x GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25';
				$dbResult = $db->read($query);
			} else {
				$dbResult = $db->read('SELECT account_id,SUM(amount) amount FROM player_hof WHERE type=' . $db->escapeString($viewType) . $gameIDSql . ' GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25');
			}
			$rows = [];
			foreach ($dbResult->records() as $dbRecord) {
				$accountID = $dbRecord->getInt('account_id');
				if ($accountID == $account->getAccountID()) {
					$foundMe = true;
				}
				$amount = HallOfFame::applyHofVisibilityMask($dbRecord->getFloat('amount'), $hofVis[$viewType], $game_id, $accountID);
				$rows[] = HallOfFame::displayHOFRow($rank++, $accountID, $game_id, $amount);
			}
			if (!$foundMe) {
				$rank = HallOfFame::getHofRank($viewType, $account->getAccountID(), $game_id);
				$rows[] = HallOfFame::displayHOFRow($rank['Rank'], $account->getAccountID(), $game_id, $rank['Amount']);
			}
			$template->assign('Rows', $rows);
		}
	}

}
