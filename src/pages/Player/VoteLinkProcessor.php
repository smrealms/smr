<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\VoteLink;
use Smr\VoteSite;

class VoteLinkProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly VoteSite $voteSite
	) {}

	public function build(AbstractSmrPlayer $player): never {
		if (!$player->getGame()->hasStarted()) {
			create_error('You cannot gain bonus turns until the game has started!');
		}

		$voteLink = new VoteLink($this->voteSite, $player->getAccountID(), $player->getGameID());
		$voteLink->setClicked();
		$voting = '<b><span class="red">v</span>o<span class="blue">t</span><span class="red">i</span>n<span class="blue">g</span></b>';
		$message = "Thank you for $voting! You will receive bonus turns once your vote is processed.";

		$container = new CurrentSector(message: $message);
		$container->go();
	}

}
