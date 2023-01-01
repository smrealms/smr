<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\Epoch;
use Smr\HardwareType;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAlliance;
use SmrForce;

class AllianceForces extends PlayerPage {

	use ReusableTrait;

	public string $file = 'alliance_forces.php';

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$allianceID = $this->allianceID;

		$alliance = SmrAlliance::getAlliance($allianceID, $player->getGameID());
		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		$db = Database::getInstance();
		$dbResult = $db->read('
		SELECT
			IFNULL(sum(mines), 0) as tot_mines,
			IFNULL(sum(combat_drones), 0) as tot_cds,
			IFNULL(sum(scout_drones), 0) as tot_sds
		FROM sector_has_forces JOIN player ON player.game_id=sector_has_forces.game_id AND sector_has_forces.owner_id=player.account_id
		WHERE player.game_id=' . $db->escapeNumber($alliance->getGameID()) . '
			AND player.alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
			AND expire_time >= ' . $db->escapeNumber(Epoch::time()));
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
		WHERE player.game_id=' . $db->escapeNumber($alliance->getGameID()) . '
		AND player.alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
		AND expire_time >= ' . $db->escapeNumber(Epoch::time()) . '
		ORDER BY sector_id ASC');

		$forces = [];
		foreach ($dbResult->records() as $dbRecord) {
			$forces[] = SmrForce::getForce($player->getGameID(), $dbRecord->getInt('sector_id'), $dbRecord->getInt('owner_id'), false, $dbRecord);
		}
		$template->assign('Forces', $forces);
	}

}
