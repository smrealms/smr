<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Epoch;
use Smr\Force;
use Smr\HardwareType;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class AllianceForces extends PlayerPage {

	use ReusableTrait;

	public string $file = 'alliance_forces.php';

	public function __construct(
		private readonly int $allianceID,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$allianceID = $this->allianceID;

		$alliance = Alliance::getAlliance($allianceID, $player->getGameID());
		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		$db = Database::getInstance();
		$dbResult = $db->read('
		SELECT
			IFNULL(sum(mines), 0) as tot_mines,
			IFNULL(sum(combat_drones), 0) as tot_cds,
			IFNULL(sum(scout_drones), 0) as tot_sds
		FROM sector_has_forces JOIN player ON player.game_id=sector_has_forces.game_id AND sector_has_forces.owner_id=player.account_id
		WHERE player.game_id = :game_id
			AND player.alliance_id = :alliance_id
			AND expire_time >= :now', [
			'game_id' => $db->escapeNumber($alliance->getGameID()),
			'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		$dbRecord = $dbResult->record();

		// Get total number of forces
		$total = [
			'Mines' => $dbRecord->getInt('tot_mines'),
			'CDs' => $dbRecord->getInt('tot_cds'),
			'SDs' => $dbRecord->getInt('tot_sds'),
		];
		$template->assign('Total', $total);

			// Get total cost of forces
		$totalCost = [
			'Mines' => $total['Mines'] * HardwareType::get(HARDWARE_MINE)->cost,
			'CDs' => $total['CDs'] * HardwareType::get(HARDWARE_COMBAT)->cost,
			'SDs' => $total['SDs'] * HardwareType::get(HARDWARE_SCOUT)->cost,
		];
		$template->assign('TotalCost', $totalCost);

		$dbResult = $db->read('
		SELECT sector_has_forces.*
		FROM player
		JOIN sector_has_forces ON player.game_id = sector_has_forces.game_id AND player.account_id = sector_has_forces.owner_id
		WHERE player.game_id = :game_id
		AND player.alliance_id = :alliance_id
		AND expire_time >= :now
		ORDER BY sector_id ASC', [
			'game_id' => $db->escapeNumber($alliance->getGameID()),
			'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
			'now' => $db->escapeNumber(Epoch::time()),
		]);

		$forces = [];
		foreach ($dbResult->records() as $dbRecord) {
			$forces[] = Force::getForce($player->getGameID(), $dbRecord->getInt('sector_id'), $dbRecord->getInt('owner_id'), false, $dbRecord);
		}
		$template->assign('Forces', $forces);
	}

}
