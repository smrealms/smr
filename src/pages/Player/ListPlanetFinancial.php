<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\PlanetList;
use Smr\Template;

class ListPlanetFinancial extends PlayerPage {

	use ReusableTrait;

	public string $file = 'planet_list_financial.php';

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		Menu::planetList($this->allianceID, 1);

		// Determine if the player can view bonds on the planet list.
		// Player can always see them if not in an alliance.
		$viewBonds = true;
		if ($this->allianceID !== 0) {
			$role_id = $player->getAllianceRole($this->allianceID);
			$db = Database::getInstance();
			$dbResult = $db->read('
				SELECT 1
				FROM alliance_has_roles
				WHERE alliance_id = :alliance_id
				AND game_id = :game_id
				AND role_id = :role_id
				AND view_bonds = 1', [
				'alliance_id' => $db->escapeNumber($this->allianceID),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'role_id' => $db->escapeNumber($role_id),
			]);
			$viewBonds = $dbResult->hasRecord();
		}
		$template->assign('CanViewBonds', $viewBonds);

		PlanetList::common($this->allianceID, $viewBonds);
	}

}
