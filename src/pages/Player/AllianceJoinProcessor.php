<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;
use SmrAlliance;

class AllianceJoinProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$alliance = SmrAlliance::getAlliance($this->allianceID, $player->getGameID());

		$joinRestriction = $alliance->getJoinRestriction($player);
		if ($joinRestriction !== false) {
			create_error($joinRestriction);
		}

		// Open recruitment implies an empty password
		if (Request::get('password', '') != $alliance->getPassword()) {
			create_error('Incorrect Password!');
		}

		// assign the player to the current alliance
		$player->joinAlliance($alliance->getAllianceID());
		$player->update();

		(new AllianceRoster($this->allianceID))->go();
	}

}
