<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Planet;
use Smr\Request;
use Smr\TradeGood;

class StockpileProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $goodID,
	) {}

	public function build(AbstractPlayer $player): never {
		$ship = $player->getShip();

		if (!$player->isLandedOnPlanet()) {
			create_error('You are not on a planet!');
		}

		$amount = Request::getInt('amount');
		if ($amount <= 0) {
			create_error('You must actually enter an amount > 0!');
		}

		$goodID = $this->goodID;

		// get a planet from the sector where the player is in
		$planet = $player->getSectorPlanet();
		$action = Request::get('action');
		if ($action === 'Ship') {
			// transfer to ship

			// do we want transfer more than we have?
			if ($amount > $planet->getStockpile($goodID)) {
				create_error('You can\'t take more than on planet!');
			}

			// do we want to transfer more than we can carry?
			if ($amount > $ship->getEmptyHolds()) {
				create_error('You can\'t take more than you can carry!');
			}

			// now transfer
			$planet->decreaseStockpile($goodID, $amount);
			$ship->increaseCargo($goodID, $amount);
			$player->log(LOG_TYPE_PLANETS, 'Player takes ' . $amount . ' ' . TradeGood::get($goodID)->name . ' from planet.');

		} elseif ($action === 'Planet') {
			// transfer to planet

			// do we want transfer more than we have?
			if ($amount > $ship->getCargo($goodID)) {
				create_error('You can\'t store more than you carry!');
			}

			// do we want to transfer more than the planet can hold?
			if ($amount > $planet->getRemainingStockpile($goodID)) {
				create_error('This planet cannot store more than ' . Planet::MAX_STOCKPILE . ' of each good!');
			}

			// now transfer
			$planet->increaseStockpile($goodID, $amount);
			$ship->decreaseCargo($goodID, $amount);
		}

		(new Stockpile())->go();
	}

}
