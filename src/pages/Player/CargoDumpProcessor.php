<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Globals;
use Plotter;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;
use Smr\TransactionType;
use SmrPort;

class CargoDumpProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $goodID,
		private readonly ?int $goodAmount = null
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$ship = $player->getShip();
		$sector = $player->getSector();

		$good_id = $this->goodID;
		$good_name = Globals::getGoodName($good_id);
		$amount = $this->goodAmount ?? Request::getInt('amount');

		if ($amount <= 0) {
			create_error('You must actually enter an amount > 0!');
		}

		if ($player->isLandedOnPlanet()) {
			create_error('You can\'t dump cargo while on a planet!');
		}

		if ($player->getTurns() < TURNS_TO_DUMP_CARGO) {
			create_error('You do not have enough turns to dump cargo!');
		}

		//lets make sure there is actually that much on the ship
		if ($amount > $ship->getCargo($good_id)) {
			create_error('You can\'t dump more than you have.');
		}

		if ($sector->offersFederalProtection()) {
			create_error('You can\'t dump cargo in a Federal Sector!');
		}

		$msg = 'You have jettisoned <span class="yellow">' . $amount . '</span> ' . pluralise($amount, 'unit', false) . ' of ' . $good_name;

		if ($player->getExperience() > 0) {
			// If they have any experience left, lose exp

			// get the distance
			$x = Globals::getGood($good_id);
			$x['TransactionType'] = TransactionType::Sell;
			$good_distance = Plotter::findDistanceToX($x, $sector, true);
			if (is_object($good_distance)) {
				$good_distance = $good_distance->getDistance();
			}
			$good_distance = max(1, $good_distance);

			// Don't lose more exp than you have
			$lost_xp = min(
				$player->getExperience(),
				IRound(SmrPort::getBaseExperience($amount, $good_distance))
			);
			$player->decreaseExperience($lost_xp);
			$player->increaseHOF($lost_xp, ['Trade', 'Experience', 'Jettisoned'], HOF_PUBLIC);

			$msg .= ' and have lost <span class="exp">' . $lost_xp . '</span> experience.';
			// log action
			$player->log(LOG_TYPE_TRADING, 'Dumps ' . $amount . ' of ' . $good_name . ' and loses ' . $lost_xp . ' experience');
		} else {
			// No experience to lose, so damage the ship
			$damage = ICeil($amount / 5);

			// Don't allow ship to be destroyed dumping cargo
			if ($ship->getArmour() <= $damage) {
				create_error('Your ship is too damaged to risk dumping cargo!');
			}

			$ship->decreaseArmour($damage);

			$msg .= '. Due to your lack of piloting experience, the cargo pierces the hull of your ship as you clumsily try to jettison the goods through the bay doors, destroying <span class="red">' . $damage . '</span> ' . pluralise($damage, 'plate', false) . ' of armour!';
			// log action
			$player->log(LOG_TYPE_TRADING, 'Dumps ' . $amount . ' of ' . $good_name . ' and takes ' . $damage . ' armour damage');
		}

		// take turn
		$player->takeTurns(TURNS_TO_DUMP_CARGO, TURNS_TO_DUMP_CARGO);

		$ship->decreaseCargo($good_id, $amount);
		$player->increaseHOF($amount, ['Trade', 'Goods', 'Jettisoned'], HOF_ALLIANCE);

		$container = new CurrentSector(message: $msg);
		$container->go();
	}

}
