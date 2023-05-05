<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Lotto;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class TraderSavings extends PlayerPage {

	use ReusableTrait;

	public string $file = 'trader_savings.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Savings');

		Menu::trader();

		$anonAccounts = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM anon_bank WHERE owner_id = :account_id AND game_id = :game_id', $player->SQLID);
		foreach ($dbResult->records() as $dbRecord) {
			$anonAccounts[] = [
				'ID' => $dbRecord->getInt('anon_id'),
				'Password' => $dbRecord->getString('password'),
			];
		}
		$template->assign('AnonAccounts', $anonAccounts);

		Lotto::checkForLottoWinner($player->getGameID());
		$template->assign('LottoInfo', Lotto::getLottoInfo($player->getGameID()));

		// Number of active lotto tickets this player has
		$dbResult = $db->read('SELECT count(*) FROM player_has_ticket WHERE ' . AbstractPlayer::SQL . ' AND time > 0', $player->SQLID);
		$tickets = $dbResult->record()->getInt('count(*)');
		$template->assign('LottoTickets', $tickets);

		// Number of active lotto tickets all players have
		$dbResult = $db->read('SELECT count(*) FROM player_has_ticket WHERE game_id = :game_id AND time > 0', [
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$tickets_tot = $dbResult->record()->getInt('count(*)');
		if ($tickets == 0) {
			$win_chance = 0;
		} else {
			$win_chance = round(100 * $tickets / $tickets_tot, 2);
		}
		$template->assign('LottoWinChance', $win_chance);

		// Number of winning lotto tickets this player has to claim
		$dbResult = $db->read('SELECT count(*) FROM player_has_ticket WHERE ' . AbstractPlayer::SQL . ' AND time = 0', $player->SQLID);
		$template->assign('WinningTickets', $dbResult->record()->getInt('count(*)'));
	}

}
