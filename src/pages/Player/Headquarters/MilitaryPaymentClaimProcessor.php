<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;

class MilitaryPaymentClaimProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractPlayer $player): never {
		if ($player->hasMilitaryPayment()) {
			$payment = $player->getMilitaryPayment();
			$player->increaseHOF($payment, ['Military Payment', 'Money', 'Claimed'], HOF_PUBLIC);

			// add to our cash
			$player->increaseCredits($payment);
			$player->setMilitaryPayment(0);

			$claimText = ('For your military activity you have been paid <span class="creds">' . number_format($payment) . '</span> credits.');
		} else {
			$claimText = ('You have done nothing worthy of military payment.');
		}

		(new MilitaryPaymentClaim($this->locationID, $claimText))->go();
	}

}
