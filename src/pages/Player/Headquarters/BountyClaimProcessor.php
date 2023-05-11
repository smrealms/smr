<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Exception;
use Smr\AbstractPlayer;
use Smr\BountyType;
use Smr\Location;
use Smr\Page\PlayerPageProcessor;

class BountyClaimProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractPlayer $player): never {
		// Determine if we're claiming Fed or UG bounties
		$location = Location::getLocation($player->getGameID(), $this->locationID);
		$bountyType = match (true) {
			$location->isHQ() => BountyType::HQ,
			$location->isUG() => BountyType::UG,
			default => throw new Exception('Location is not HQ or UG'),
		};
		$bounties = $player->getClaimableBounties($bountyType);

		if (count($bounties) > 0) {
			$claimText = ('You have claimed the following bounties<br /><br />');

			foreach ($bounties as $bounty) {
				// get bounty id from db
				$amount = $bounty->getCredits();
				$smrCredits = $bounty->getSmrCredits();
				// no interest on bounties
				// $time = Smr\Epoch::time();
				// $days = ($time - $db->getField('time')) / 60 / 60 / 24;
				// $amount = round($db->getField('amount') * pow(1.05,$days));

				// add bounty to our cash
				$player->increaseCredits($amount);
				$player->getAccount()->increaseSmrCredits($smrCredits);
				$claimText .= ($bounty->getTargetPlayer()->getDisplayName() . ' : <span class="creds">' . number_format($amount) . '</span> credits and <span class="red">' . number_format($smrCredits) . '</span> SMR credits<br />');

				// add HoF stat
				$player->increaseHOF(1, ['Bounties', 'Claimed', 'Results'], HOF_PUBLIC);
				$player->increaseHOF($amount, ['Bounties', 'Claimed', 'Money'], HOF_PUBLIC);
				$player->increaseHOF($smrCredits, ['Bounties', 'Claimed', 'SMR Credits'], HOF_PUBLIC);

				// delete bounty
				$bounty->setClaimed();
				$bounty->update();
			}
		} else {
			$claimText = ('You have no claimable bounties<br /><br />');
		}

		(new BountyClaim($this->locationID, $claimText))->go();
	}

}
