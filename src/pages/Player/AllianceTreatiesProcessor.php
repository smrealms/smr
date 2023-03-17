<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class AllianceTreatiesProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $otherAllianceID,
		private readonly bool $accept,
		private readonly bool $allianceBankAccess = false
	) {}

	public function build(AbstractPlayer $player): never {
		//get the alliances
		if (!$player->hasAlliance()) {
			create_error('You are not in an alliance!');
		}
		$alliance_id_1 = $this->otherAllianceID;
		$alliance_id_2 = $player->getAllianceID();

		$db = Database::getInstance();
		if ($this->accept) {
			$db->update(
				'alliance_treaties',
				['official' => 'TRUE'],
				[
					'alliance_id_1' => $alliance_id_1,
					'alliance_id_2' => $alliance_id_2,
					'game_id' => $player->getGameID(),
				],
			);

			if ($this->allianceBankAccess) {
				//make an AA role for both alliances, use treaty_created column
				$pairs = [
					$alliance_id_1 => $alliance_id_2,
					$alliance_id_2 => $alliance_id_1,
				];
				foreach ($pairs as $alliance_id_A => $alliance_id_B) {
					// get last id
					$dbResult = $db->read('SELECT IFNULL(MAX(role_id), 0) as max_role_id
								FROM alliance_has_roles
								WHERE game_id = :game_id
									AND alliance_id = :alliance_id', [
						'game_id' => $db->escapeNumber($player->getGameID()),
						'alliance_id' => $db->escapeNumber($alliance_id_A),
					]);
					$role_id = $dbResult->record()->getInt('max_role_id') + 1;

					$allianceName = Alliance::getAlliance($alliance_id_B, $player->getGameID())->getAllianceName();
					$db->insert('alliance_has_roles', [
						'alliance_id' => $alliance_id_A,
						'game_id' => $player->getGameID(),
						'role_id' => $role_id,
						'role' => $allianceName,
						'treaty_created' => 1,
					]);
				}
			}
		} else {
			$db->delete('alliance_treaties', [
				'alliance_id_1' => $alliance_id_1,
				'alliance_id_2' => $alliance_id_2,
				'game_id' => $player->getGameID(),
			]);
		}

		$container = new AllianceTreaties();
		$container->go();
	}

}
