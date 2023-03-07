<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceLeadershipProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$alliance = $player->getAlliance();

		$leader_id = Request::getInt('leader_id');
		$alliance->setLeaderID($leader_id);
		$alliance->update();

		$db = Database::getInstance();
		$db->update(
			'player_has_alliance_role',
			['role_id' => $db->escapeNumber(ALLIANCE_ROLE_NEW_MEMBER)],
			[
				...$player->SQLID,
				'alliance_id' => $db->escapeNumber($player->getAllianceID()),
			],
		);
		$db->update(
			'player_has_alliance_role',
			['role_id' => $db->escapeNumber(ALLIANCE_ROLE_LEADER)],
			[
				'account_id' => $db->escapeNumber($leader_id),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'alliance_id' => $db->escapeNumber($player->getAllianceID()),
			],
		);

		// Notify the new leader
		$playerMessage = 'You are now the leader of ' . $alliance->getAllianceBBLink() . '!';
		$player->sendMessageFromAllianceCommand($leader_id, $playerMessage);

		(new AllianceRoster())->go();
	}

}
