<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class AllianceLeaveProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$alliance = $player->getAlliance();

		if ($player->isAllianceLeader() && $alliance->getNumMembers() > 1) {
			create_error('You are the leader! You must hand over leadership first!');
		}

		// will this alliance be empty if we leave? (means one member right now)
		// Don't delete the Newbie Help Alliance!
		if ($alliance->getNumMembers() == 1 && !$alliance->isNHA()) {
			// Retain the alliance, but delete some auxilliary info
			$db = Database::getInstance();
			$sql = 'alliance_id = :alliance_id AND game_id = :game_id';
			$sqlParams = [
				'alliance_id' => $db->escapeNumber($player->getAllianceID()),
				'game_id' => $db->escapeNumber($player->getGameID()),
			];
			$db->write('DELETE FROM alliance_bank_transactions WHERE ' . $sql, $sqlParams);
			$db->write('DELETE FROM alliance_thread WHERE ' . $sql, $sqlParams);
			$db->write('DELETE FROM alliance_thread_topic WHERE ' . $sql, $sqlParams);
			$db->write('DELETE FROM alliance_has_roles WHERE ' . $sql, $sqlParams);
			$db->write('UPDATE alliance SET leader_id = 0, discord_channel = NULL
			            WHERE ' . $sql, $sqlParams);
		}

		// now leave the alliance
		$player->leaveAlliance();

		$container = new CurrentSector();
		$container->go();
	}

}
