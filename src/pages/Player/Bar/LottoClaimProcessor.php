<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Lotto;
use Smr\Page\PlayerPageProcessor;

class LottoClaimProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(AbstractPlayer $player): never {
		$message = '';
		//check if we really are a winner
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM player_has_ticket WHERE ' . AbstractPlayer::SQL . ' AND time = 0', $player->SQLID);
		if ($dbResult->hasRecord()) {
			$prize = $dbResult->record()->getInt('prize');
			$NHLAmount = IFloor(($prize - Lotto::TICKET_COST) * (1 - Lotto::WIN_FRAC)); // NHL gets leftover after winner's cut
			$db->write('UPDATE player SET bank = bank + :nhl_amount WHERE account_id = :nhl_id AND game_id = :game_id', [
				'nhl_amount' => $db->escapeNumber($NHLAmount),
				'nhl_id' => $db->escapeNumber(ACCOUNT_ID_NHL),
				'game_id' => $db->escapeNumber($player->getGameID()),
			]);
			$player->increaseCredits($prize);
			$player->increaseHOF($prize, ['Bar', 'Lotto', 'Money', 'Claimed'], HOF_PUBLIC);
			$player->increaseHOF(1, ['Bar', 'Lotto', 'Results', 'Claims'], HOF_PUBLIC);
			$message .= '<div class="center">You have claimed <span class="red">$' . number_format($prize) . '</span>!<br /></div><br />';
			$db->delete('player_has_ticket', [
				...$player->SQLID,
				'prize' => $prize,
				'time' => 0,
			]);
			$db->delete('news', [
				'type' => 'lotto',
				'game_id' => $player->getGameID(),
			]);
		}
		//offer another drink and such
		$container = new BarMain($this->locationID, $message);
		$container->go();
	}

}
