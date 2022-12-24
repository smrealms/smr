<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use SmrAlliance;

class AllianceTreatiesProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $otherAllianceID,
		private readonly bool $accept,
		private readonly bool $allianceBankAccess = false
	) {}

	public function build(AbstractSmrPlayer $player): never {
		//get the alliances
		if (!$player->hasAlliance()) {
			create_error('You are not in an alliance!');
		}
		$alliance_id_1 = $this->otherAllianceID;
		$alliance_id_2 = $player->getAllianceID();

		$db = Database::getInstance();
		if ($this->accept) {
			$db->write('UPDATE alliance_treaties SET official = \'TRUE\' WHERE alliance_id_1 = ' . $db->escapeNumber($alliance_id_1) . ' AND alliance_id_2 = ' . $db->escapeNumber($alliance_id_2) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));

			if ($this->allianceBankAccess) {
				//make an AA role for both alliances, use treaty_created column
				$pairs = [
					$alliance_id_1 => $alliance_id_2,
					$alliance_id_2 => $alliance_id_1,
				];
				foreach ($pairs as $alliance_id_A => $alliance_id_B) {
					// get last id
					$dbResult = $db->read('SELECT MAX(role_id)
								FROM alliance_has_roles
								WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
									AND alliance_id = ' . $db->escapeNumber($alliance_id_A));
					$role_id = $dbResult->record()->getInt('MAX(role_id)') + 1;

					$allianceName = SmrAlliance::getAlliance($alliance_id_B, $player->getGameID())->getAllianceName();
					$db->insert('alliance_has_roles', [
						'alliance_id' => $db->escapeNumber($alliance_id_A),
						'game_id' => $db->escapeNumber($player->getGameID()),
						'role_id' => $db->escapeNumber($role_id),
						'role' => $db->escapeString($allianceName),
						'treaty_created' => 1,
					]);
				}
			}
		} else {
			$db->write('DELETE FROM alliance_treaties WHERE alliance_id_1 = ' . $db->escapeNumber($alliance_id_1) . ' AND alliance_id_2 = ' . $db->escapeNumber($alliance_id_2) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
		}

		$container = new AllianceTreaties();
		$container->go();
	}

}