<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class AllianceLeadershipProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$alliance = $player->getAlliance();

		$leaderPlayerID = Request::getInt('leader_player_id');
		$leaderPlayer = Player::getPlayer($leaderPlayerID);
		if (!$player->sameAlliance($leaderPlayer)) {
			throw new Exception('Cannot make a player from another alliance its leader.');
		}
		$alliance->setLeaderPlayerID($leaderPlayerID);
		$alliance->update();

		$db = Database::getInstance();
		$db->update(
			'player_has_alliance_role',
			['role_id' => ALLIANCE_ROLE_NEW_MEMBER],
			[
				...$player->SQLID,
				'alliance_id' => $player->getAllianceID(),
			],
		);
		$db->update(
			'player_has_alliance_role',
			['role_id' => ALLIANCE_ROLE_LEADER],
			[
				'player_id' => $leaderPlayerID,
				'alliance_id' => $player->getAllianceID(),
			],
		);

		// Notify the new leader
		$playerMessage = 'You are now the leader of ' . $alliance->getAllianceBBLink() . '!';
		$player->sendMessageFromAllianceCommand($leaderPlayerID, $playerMessage);

		new AllianceRoster()->go();
	}

}
