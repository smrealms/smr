<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class BountyPlaceProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractPlayer $player): never {
		$amount = Request::getInt('amount');
		$smrCredits = Request::getInt('smrcredits');

		if ($player->getCredits() < $amount) {
			create_error('You dont have that much money.');
		}

		if ($player->getAccount()->getSmrCredits() < $smrCredits) {
			create_error('You dont have that many SMR credits.');
		}

		if ($amount <= 0 && $smrCredits <= 0) {
			create_error('You must enter an amount greater than 0!');
		}

		$container = new BountyPlaceConfirm(
			locationID: $this->locationID,
			otherPlayerID: Request::getInt('player_id'),
			credits: $amount,
			smrCredits: $smrCredits,
		);
		$container->go();
	}

}
