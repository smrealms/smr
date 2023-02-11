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
		$query = 'UPDATE player_has_alliance_role SET role_id = :role_id WHERE ' . AbstractPlayer::SQL . ' AND alliance_id = :alliance_id';
		$db->write($query, [
			...$player->SQLID,
			'alliance_id' => $db->escapeNumber($player->getAllianceID()),
			'role_id' => $db->escapeNumber(ALLIANCE_ROLE_NEW_MEMBER),
		]);
		$db->write($query, [
			'account_id' => $db->escapeNumber($leader_id),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'alliance_id' => $db->escapeNumber($player->getAllianceID()),
			'role_id' => $db->escapeNumber(ALLIANCE_ROLE_LEADER),
		]);

		// Notify the new leader
		$playerMessage = 'You are now the leader of ' . $alliance->getAllianceBBLink() . '!';
		$player->sendMessageFromAllianceCommand($leader_id, $playerMessage);

		(new AllianceRoster())->go();
	}

}
