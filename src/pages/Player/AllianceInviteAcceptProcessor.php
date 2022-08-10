<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Exceptions\AllianceInvitationNotFound;
use Smr\Page\PlayerPageProcessor;
use SmrAlliance;
use SmrInvitation;

class AllianceInviteAcceptProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		// Check that the invitation is registered in the database
		try {
			$invite = SmrInvitation::get($this->allianceID, $player->getGameID(), $player->getAccountID());
		} catch (AllianceInvitationNotFound) {
			create_error('Your invitation to join this alliance has expired or been canceled!');
		}

		// Make sure the player can join the new alliance before leaving the current one
		$newAlliance = SmrAlliance::getAlliance($this->allianceID, $player->getGameID());
		$joinRestriction = $newAlliance->getJoinRestriction($player, false);
		if ($joinRestriction !== false) {
			create_error($joinRestriction);
		}

		// Leave current alliance
		if ($player->hasAlliance()) {
			if ($player->isAllianceLeader()) {
				create_error('You are the alliance leader! You must handover leadership first.');
			}
			$player->leaveAlliance();
		}

		// Join new alliance
		$player->joinAlliance($newAlliance->getAllianceID());

		// Delete the invitation now that the player has joined
		$invite->delete();

		$container = new AllianceMotd($this->allianceID);
		$container->go();
	}

}
