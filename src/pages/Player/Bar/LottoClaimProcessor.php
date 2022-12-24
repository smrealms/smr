<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class LottoClaimProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$message = '';
		//check if we really are a winner
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM player_has_ticket WHERE ' . $player->getSQL() . ' AND time = 0');
		if ($dbResult->hasRecord()) {
			$prize = $dbResult->record()->getInt('prize');
			$NHLAmount = ($prize - 1000000) / 9;
			$db->write('UPDATE player SET bank = bank + ' . $db->escapeNumber($NHLAmount) . ' WHERE account_id = ' . $db->escapeNumber(ACCOUNT_ID_NHL) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
			$player->increaseCredits($prize);
			$player->increaseHOF($prize, ['Bar', 'Lotto', 'Money', 'Claimed'], HOF_PUBLIC);
			$player->increaseHOF(1, ['Bar', 'Lotto', 'Results', 'Claims'], HOF_PUBLIC);
			$message .= '<div class="center">You have claimed <span class="red">$' . number_format($prize) . '</span>!<br /></div><br />';
			$db->write('DELETE FROM player_has_ticket WHERE ' . $player->getSQL() . ' AND prize = ' . $db->escapeNumber($prize) . ' AND time = 0');
			$db->write('DELETE FROM news WHERE type = \'lotto\' AND game_id = ' . $db->escapeNumber($player->getGameID()));
		}
		//offer another drink and such
		$container = new BarMain($this->locationID, $message);
		$container->go();
	}

}