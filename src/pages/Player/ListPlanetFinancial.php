<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\PlanetList;
use Smr\Player;
use Smr\Template;

class ListPlanetFinancial extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private readonly int $allianceID,
	) {}

	public function build(Player $player, Template $template): void {
		Menu::planetList($this->allianceID, 1);

		// Determine if the player can view bonds on the planet list.
		// Player can always see them if not in an alliance.
		$viewBonds = true;
		if ($this->allianceID !== 0) {
			$role_id = $player->getAllianceRole($this->allianceID);
			$db = Database::getInstance();
			$dbResult = $db->select('alliance_has_roles', [
				'view_bonds' => $db->escapeBoolean(true),
				'alliance_id' => $this->allianceID,
				'game_id' => $player->getGameID(),
				'role_id' => $role_id,
			]);
			$viewBonds = $dbResult->hasRecord();
		}

		$planetList = PlanetList::common($this->allianceID, $viewBonds);
		$template->pageTopic = ($planetList['Alliance'] === null)
			? 'Planet'
			: 'Planets : ' . $planetList['Alliance']->getAllianceDisplayName();

		$template->pageRenderer = fn() => ListPlanetFinancialRenderer::render(
			template: $template,
			CanViewBonds: $viewBonds,
			Alliance: $planetList['Alliance'],
			AllPlanets: $planetList['AllPlanets'],
			PlayerPlanet: $planetList['PlayerPlanet'],
			ThisPlayer: $player,
		);
	}

}
