<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceLeadershipProcessor extends PlayerPageProcessor {

	public function build(AbstractSmrPlayer $player): never {
		$alliance = $player->getAlliance();

		$leader_id = Request::getInt('leader_id');
		$alliance->setLeaderID($leader_id);
		$alliance->update();

		$db = Database::getInstance();
		$db->write('UPDATE player_has_alliance_role SET role_id = ' . $db->escapeNumber(ALLIANCE_ROLE_NEW_MEMBER) . ' WHERE ' . $player->getSQL() . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));
		$db->write('UPDATE player_has_alliance_role SET role_id = ' . $db->escapeNumber(ALLIANCE_ROLE_LEADER) . ' WHERE account_id = ' . $db->escapeNumber($leader_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($player->getAllianceID()));

		// Notify the new leader
		$playerMessage = 'You are now the leader of ' . $alliance->getAllianceBBLink() . '!';
		$player->sendMessageFromAllianceCommand($leader_id, $playerMessage);

		(new AllianceRoster())->go();
	}

}
