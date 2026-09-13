<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Exceptions\PlayerNotFound;
use Smr\Game;
use Smr\HallOfFame;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Shared\HallOfFameRenderer;
use Smr\Player;
use Smr\Template;

class HallOfFameAll extends AccountPage {

	use ReusableTrait;
	public function __construct(
		private readonly ?int $gameID = null,
		public readonly ?string $viewType = null,
	) {}

	/**
	 * Construct a new object with the same properties, but a different
	 * viewType.
	 */
	public function withViewType(?string $viewType): self {
		return new self($this->gameID, $viewType);
	}

	public function build(Account $account, Template $template): void {
		$game_id = $this->gameID;

		if ($game_id === null) {
			$topic = 'All Time Hall of Fame';
		} else {
			$topic = 'Hall of Fame: ' . Game::getGame($game_id)->getDisplayName();
		}
		$template->pageTopic = $topic;

		// Get game player for viewing account
		if ($game_id === null) {
			$player = null;
		} else {
			try {
				$player = Player::getPlayerByAccountAndGame($account->getAccountID(), $game_id);
			} catch (PlayerNotFound) {
				$player = null;
			}
		}

		// We will only show viewing account's rank if all-time or account joined game
		$hasRank = $game_id === null || $player !== null;

		$breadcrumb = HallOfFame::buildBreadcrumb($this, $game_id !== null ? 'Current HoF' : 'Global HoF');

		$viewType = $this->viewType;
		$hofVis = Player::getHOFVis();

		if ($viewType === null || !isset($hofVis[$viewType])) {
			// Not a complete HOF type, so continue to show categories
			$allowedVis = [HOF_PUBLIC, HOF_ALLIANCE];
			$categories = HallOfFame::getHofCategories(
				page: $this,
				allowedVis: $allowedVis,
				game_id: $game_id,
				rankAccountID: $hasRank ? $account->getAccountID() : null,
			);
			$rows = null;

		} else {
			// Rankings page
			$categories = null;
			$db = Database::getInstance();
			$gameIDSql = ' AND IF(:game_id IS NULL, player_hof.game_id IN (SELECT game_id FROM game WHERE end_time < :now AND ignore_stats = \'FALSE\'), player_hof.game_id = :game_id)';
			$gameIDParams = [
				'game_id' => $game_id,
				'now' => Epoch::time(),
			];

			$rank = 1;
			$foundMe = false;

			if ($viewType === HOF_TYPE_DONATION) {
				$dbResult = $db->read('SELECT account_id, SUM(amount) as amount FROM account_donated
							GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25');
			} elseif ($viewType === HOF_TYPE_USER_SCORE) {
				$statements = Account::getUserScoreCaseStatement();
				$query = 'SELECT account_id, ' . $statements['CASE'] . ' amount FROM (SELECT player.account_id, type, SUM(amount) amount FROM player_hof JOIN player USING (player_id) WHERE type IN (:hof_types)' . $gameIDSql . ' GROUP BY player.account_id,type) x GROUP BY account_id ORDER BY amount DESC, account_id ASC LIMIT 25';
				$dbResult = $db->read($query, [
					'hof_types' => $db->escapeArray($statements['IN']),
					...$gameIDParams,
				]);
			} else {
				$dbResult = $db->read('SELECT player.account_id,SUM(amount) amount FROM player_hof JOIN player USING (player_id) WHERE type = :hof_type ' . $gameIDSql . ' GROUP BY player.account_id ORDER BY amount DESC, account_id ASC LIMIT 25', [
					'hof_type' => $db->escapeString($viewType),
					...$gameIDParams,
				]);
			}
			$rows = [];
			foreach ($dbResult->records() as $dbRecord) {
				$accountID = $dbRecord->getInt('account_id');
				if ($accountID === $account->getAccountID()) {
					$foundMe = true;
				}
				$amount = HallOfFame::applyHofVisibilityMask($dbRecord->getFloat('amount'), $hofVis[$viewType], $game_id, $accountID);
				$rows[] = HallOfFame::displayHOFRow($rank++, $accountID, $game_id, $amount);
			}
			// Add viewer row if not already found (if all-time or joined game)
			if (!$foundMe && $hasRank) {
				$rank = HallOfFame::getHofRank($viewType, $account->getAccountID(), $game_id);
				$rows[] = HallOfFame::displayHOFRow($rank['Rank'], $account->getAccountID(), $game_id, $rank['Amount']);
			}
		}

		$template->pageRenderer = fn() => HallOfFameRenderer::render(
			PersonalHofHREF: $player?->getPersonalHofHREF(),
			Breadcrumb: $breadcrumb,
			Categories: $categories,
			Rows: $rows,
			ThisAccount: $account,
		);
	}

}
