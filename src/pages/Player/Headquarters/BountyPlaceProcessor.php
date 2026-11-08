<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class BountyPlaceProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(Player $player): never {
		$amount = Request::getInt('amount', 0);
		$smrCredits = Request::getInt('smrcredits', 0);

		if ($player->getCredits() < $amount) {
			create_error('You dont have that much money.');
		}

		if ($player->getAccount()->getSmrCredits() < $smrCredits) {
			create_error('You dont have that many SMR credits.');
		}

		if ($amount <= 0 && $smrCredits <= 0) {
			create_error('You must enter an amount greater than 0!');
		}

		$otherPlayer = Player::getPlayerByPlayerNumber(Request::getInt('player_number'), $player->getGameID());
		new BountyPlaceConfirm(
			locationID: $this->locationID,
			otherPlayerID: $otherPlayer->getPlayerID(),
			credits: $amount,
			smrCredits: $smrCredits,
		)->go();
	}

}
