<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Exceptions\PlayerNotFound;
use Smr\Game;
use Smr\HallOfFame;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class HallOfFamePersonal extends AccountPage {

	use ReusableTrait;

	public string $file = 'hall_of_fame_player_detail.php';

	public function __construct(
		private readonly int $hofAccountID,
		private readonly ?int $gameID = null,
		public readonly ?string $viewType = null
	) {}

	/**
	 * Construct a new object with the same properties, but a different
	 * viewType.
	 */
	public function withViewType(?string $viewType): self {
		return new self($this->hofAccountID, $this->gameID, $viewType);
	}

	public function build(Account $account, Template $template): void {
		$account_id = $this->hofAccountID;
		$game_id = $this->gameID;
		$player = null;

		if (isset($game_id)) {
			try {
				$player = Player::getPlayer($account->getAccountID(), $game_id);
			} catch (PlayerNotFound) {
				// Session user is not in this game, $player remains null
			}

			try {
				$hofPlayer = Player::getPlayer($account_id, $game_id);
			} catch (PlayerNotFound) {
				create_error('That player has not yet joined this game.');
			}
			$template->assign('PageTopic', htmlentities($hofPlayer->getPlayerName()) . '\'s Personal Hall of Fame: ' . Game::getGame($game_id)->getDisplayName());
		} else {
			$hofName = Account::getAccount($account_id)->getHofDisplayName();
			$template->assign('PageTopic', $hofName . '\'s All Time Personal Hall of Fame');
		}

		$breadcrumb = HallOfFame::buildBreadcrumb($this, 'Personal HoF');
		$template->assign('Breadcrumb', $breadcrumb);

		$viewType = $this->viewType ?? '';
		$hofVis = Player::getHOFVis();

		if (!isset($hofVis[$viewType])) {
			// Not a complete HOF type, so continue to show categories
			$allowedVis = [HOF_PUBLIC];
			if ($account->getAccountID() === $account_id) {
				$allowedVis[] = HOF_ALLIANCE;
				$allowedVis[] = HOF_PRIVATE;
			} elseif (isset($hofPlayer) && $hofPlayer->sameAlliance($player)) {
				$allowedVis[] = HOF_ALLIANCE;
			}
			$categories = HallOfFame::getHofCategories($this, $allowedVis, $game_id, $account_id);
			$template->assign('Categories', $categories);

		} else {
			// Rankings page
			$hofRank = HallOfFame::getHofRank($viewType, $account_id, $game_id);
			$rows = [HallOfFame::displayHOFRow($hofRank['Rank'], $account_id, $game_id, $hofRank['Amount'])];

			if ($account->getAccountID() !== $account_id) {
				//current player's score.
				$playerRank = HallOfFame::getHofRank($viewType, $account->getAccountID(), $game_id);
				$row = HallOfFame::displayHOFRow($playerRank['Rank'], $account->getAccountID(), $game_id, $playerRank['Amount']);
				if ($playerRank['Rank'] >= $hofRank['Rank']) {
					$rows[] = $row;
				} else {
					array_unshift($rows, $row);
				}
			}
			$template->assign('Rows', $rows);
		}
	}

}
