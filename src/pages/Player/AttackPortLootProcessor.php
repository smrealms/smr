<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Globals;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AttackPortLootProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $goodID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$ship = $player->getShip();

		$good_id = $this->goodID;
		$amount = Request::getInt('amount');
		if ($amount <= 0) {
			create_error('You must enter an amount > 0!');
		}

		$port = $player->getSectorPort();
		// check if there are enough left at port
		if ($port->getGoodAmount($good_id) < $amount) {
			create_error('There isn\'t that much to loot.');
		}

		// check if we have enough room for the thing we are going to buy
		if ($amount > $ship->getEmptyHolds()) {
			create_error('Scanning your ship indicates that you do not have enough free cargo bays!');
		}

		// do we have enough turns?
		if ($player->getTurns() == 0) {
			create_error('You don\'t have enough turns to loot.');
		}

		$player->log(LOG_TYPE_TRADING, 'Player Loots ' . $amount . ' ' . Globals::getGoodName($good_id));
		$ship->increaseCargo($good_id, $amount);
		$port->decreaseGoodAmount($good_id, $amount);

		$container = new AttackPortLoot();
		$container->go();
	}

}
