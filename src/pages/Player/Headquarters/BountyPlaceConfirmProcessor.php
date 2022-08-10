<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use AbstractSmrPlayer;
use Exception;
use Smr\BountyType;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;
use SmrLocation;
use SmrPlayer;

class BountyPlaceConfirmProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID,
		private readonly int $otherAccountID,
		private readonly int $credits,
		private readonly int $smrCredits
	) {}

	public function build(AbstractSmrPlayer $player): never {
		if (!$player->getSector()->hasLocation($this->locationID)) {
			create_error('That location does not exist in this sector');
		}

		$location = SmrLocation::getLocation($player->getGameID(), $this->locationID);

		[$type, $body] = match (true) {
			$location->isHQ() => [BountyType::HQ, Government::class],
			$location->isUG() => [BountyType::UG, Underground::class],
			default => throw new Exception('Location is not HQ or UG'),
		};
		$container = new $body($this->locationID);

		// if we don't have a yes we leave immediatly
		if (Request::get('action') != 'Yes') {
			$container->go();
		}

		// get values from container (validated in bounty_place_processing.php)
		$amount = $this->credits;
		$smrCredits = $this->smrCredits;
		$account_id = $this->otherAccountID;

		// take the bounty from the cash
		$player->decreaseCredits($amount);
		$player->getAccount()->decreaseSmrCredits($smrCredits);

		$player->increaseHOF($smrCredits, ['Bounties', 'Placed', 'SMR Credits'], HOF_PUBLIC);
		$player->increaseHOF($amount, ['Bounties', 'Placed', 'Money'], HOF_PUBLIC);
		$player->increaseHOF(1, ['Bounties', 'Placed', 'Number'], HOF_PUBLIC);

		$placed = SmrPlayer::getPlayer($account_id, $player->getGameID());
		$placed->increaseCurrentBountyAmount($type, $amount);
		$placed->increaseCurrentBountySmrCredits($type, $smrCredits);
		$placed->increaseHOF($smrCredits, ['Bounties', 'Received', 'SMR Credits'], HOF_PUBLIC);
		$placed->increaseHOF($amount, ['Bounties', 'Received', 'Money'], HOF_PUBLIC);
		$placed->increaseHOF(1, ['Bounties', 'Received', 'Number'], HOF_PUBLIC);

		//Update for top bounties list
		$player->update();
		$player->getAccount()->update();
		$placed->update();
		$container->go();
	}

}
