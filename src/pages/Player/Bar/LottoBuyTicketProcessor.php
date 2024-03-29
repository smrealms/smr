<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Lotto;
use Smr\Page\PlayerPageProcessor;

class LottoBuyTicketProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		if ($player->getCredits() < Lotto::TICKET_COST) {
			create_error('There once was a trader with less than $' . number_format(Lotto::TICKET_COST) . ' ...wait...thats you!');
		}

		$time = Epoch::time();
		while (true) {
			//avoid double entries (since table is unique on game,account,time)
			$dbResult = $db->read('SELECT 1 FROM player_has_ticket WHERE ' . AbstractPlayer::SQL . ' AND time = :time', [
				...$player->SQLID,
				'time' => $db->escapeNumber($time),
			]);
			if (!$dbResult->hasRecord()) {
				break;
			}
			$time++;
		}

		$db->insert('player_has_ticket', [
			'game_id' => $player->getGameID(),
			'account_id' => $player->getAccountID(),
			'time' => $time,
		]);
		$player->decreaseCredits(Lotto::TICKET_COST);
		$player->increaseHOF(Lotto::TICKET_COST, ['Bar', 'Lotto', 'Money', 'Spent'], HOF_PUBLIC);
		$player->increaseHOF(1, ['Bar', 'Lotto', 'Tickets Bought'], HOF_PUBLIC);
		$dbResult = $db->read('SELECT count(*) as num FROM player_has_ticket WHERE ' . AbstractPlayer::SQL . ' AND time > 0 GROUP BY account_id', $player->SQLID);
		$num = $dbResult->record()->getInt('num');
		$message = ('<div class="center">Thanks for your purchase and good luck!  You currently');
		$message .= (' own ' . pluralise($num, 'ticket') . '!</div><br />');

		$container = new BarMain($this->locationID, $message);
		$container->go();
	}

}
