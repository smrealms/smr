<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class AllianceLeaveProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$alliance = $player->getAlliance();

		if ($player->isAllianceLeader() && $alliance->getNumMembers() > 1) {
			create_error('You are the leader! You must hand over leadership first!');
		}

		// now leave the alliance
		$player->leaveAlliance();

		// Disband this alliance if it is now empty.
		// Don't delete the Newbie Help Alliance!
		if ($alliance->getNumMembers() === 0 && !$alliance->isNHA()) {
			// Retain the alliance, but delete some auxilliary info
			$db = Database::getInstance();
			$db->delete('alliance_bank_transactions', $alliance->SQLID);
			$db->delete('alliance_thread', $alliance->SQLID);
			$db->delete('alliance_thread_topic', $alliance->SQLID);
			$db->delete('alliance_has_roles', $alliance->SQLID);
			$alliance->setLeaderID(0);
			$alliance->setDiscordChannel(null);
			$alliance->update();
		}

		$container = new CurrentSector();
		$container->go();
	}

}
