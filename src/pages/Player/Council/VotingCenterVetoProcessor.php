<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\AbstractPlayer;
use Smr\CouncilVoting;
use Smr\Page\PlayerPageProcessor;

class VotingCenterVetoProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $otherRaceID,
		private readonly string $voteType,
	) {}

	public function build(AbstractPlayer $player): never {
		if (!$player->isPresident()) {
			create_error('You have to be the president to veto!');
		}

		CouncilVoting::deleteVote(
			race_id_1: $player->getRaceID(),
			race_id_2: $this->otherRaceID,
			gameID: $player->getGameID(),
			type: $this->voteType,
		);

		(new VotingCenter())->go();
	}

}
