<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Lotto;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class TraderSavings extends PlayerPage {

	use ReusableTrait;

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Savings';

		Menu::trader();

		$anonAccounts = [];
		$db = Database::getInstance();
		$dbResult = $db->select('anon_bank', [
			'owner_id' => $player->getAccountID(),
			'game_id' => $player->getGameID(),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$anonAccounts[] = [
				'ID' => $dbRecord->getInt('anon_id'),
				'Password' => $dbRecord->getString('password'),
			];
		}
		Lotto::checkForLottoWinner($player->getGameID());
		$lottoInfo = Lotto::getLottoInfo($player->getGameID());

		// Number of active lotto tickets this player has
		$dbResult = $db->read('SELECT count(*) FROM player_has_ticket WHERE ' . Player::SQL . ' AND time > 0', $player->SQLID);
		$tickets = $dbResult->record()->getInt('count(*)');
		// Number of active lotto tickets all players have
		$dbResult = $db->read('SELECT count(*) FROM player_has_ticket WHERE game_id = :game_id AND time > 0', [
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$tickets_tot = $dbResult->record()->getInt('count(*)');
		if ($tickets === 0) {
			$win_chance = 0;
		} else {
			$win_chance = round(100 * $tickets / $tickets_tot, 2);
		}
		// Number of winning lotto tickets this player has to claim
		$numToClaim = $db->count('player_has_ticket', ['time' => 0, ...$player->SQLID]);
		$template->pageRenderer = fn() => TraderSavingsRenderer::render(
			AnonAccounts: $anonAccounts,
			LottoTickets: $tickets,
			LottoInfo: $lottoInfo,
			LottoWinChance: $win_chance,
			WinningTickets: $numToClaim,
		);
	}

}
