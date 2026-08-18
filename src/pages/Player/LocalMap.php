<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Request;
use Smr\Template;

class LocalMap extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		if ($player->isLandedOnPlanet()) {
			create_error('You are on a planet!');
		}

		// Create a session to store temporary display options
		// Do not garbage collect here for best performance (see map_galaxy.php).
		if (!session_start(['gc_probability' => 0, 'gc_maxlifetime' => 86400])) {
			throw new Exception('Failed to start session');
		}

		// Set temporary options
		if ($player->hasAlliance()) {
			if (Request::has('change_settings')) {
				$_SESSION['show_seedlist_sectors'] = Request::has('show_seedlist_sectors');
				$_SESSION['hide_allied_forces'] = Request::has('hide_allied_forces');
			}
			$showSeedlistSectors = $_SESSION['show_seedlist_sectors'] ?? false;
			$hideAlliedForces = $_SESSION['hide_allied_forces'] ?? false;
			$CheckboxFormHREF = ''; // Submit to same page
		} else {
			$hideAlliedForces = true;
			$showSeedlistSectors = false;
			$CheckboxFormHREF = null;
		}

		$template->spaceView = true;

		$galaxy = $player->getSector()->getGalaxy();

		$mapSectors = $galaxy->getMapSectors($player->getSectorID(), $player->getZoom());

		$template->pageRenderer = fn() => LocalMapRenderer::render(
			ShowSeedlistSectors: $showSeedlistSectors,
			HideAlliedForces: $hideAlliedForces,
			CheckboxFormHREF: $CheckboxFormHREF,
			MapExpandHREF: new LocalMapProcessor('Expand')->href(),
			MapShrinkHREF: new LocalMapProcessor('Shrink')->href(),
			GalaxyName: $galaxy->getDisplayName(),
			MapSectors: $mapSectors,
			ThisPlayer: $player,
		);
	}

}
