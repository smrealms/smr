<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class BuyTickerProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$account = $player->getAccount();

		if ($account->getTotalSmrCredits() < CREDITS_PER_TICKER) {
			create_error('You don\'t have enough SMR Credits. Donate to SMR to gain SMR Credits!');
		}
		$type = Request::get('type');
		$expires = Epoch::time();
		$ticker = $player->getTicker($type);
		if ($ticker !== false) {
			$expires = $ticker['Expires'];
		}
		$expires += 5 * 86400;

		$db = Database::getInstance();
		$db->replace('player_has_ticker', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'type' => $db->escapeString($type),
			'expires' => $db->escapeNumber($expires),
		]);

		//take credits
		$account->decreaseTotalSmrCredits(CREDITS_PER_TICKER);

		//offer another drink and such
		$message = '<div class="center">Your system has been added.  Enjoy!</div><br />';
		$container = new BarMain($this->locationID, $message);
		$container->go();
	}

}
