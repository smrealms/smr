<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Request;
use Smr\Template;
use SmrAccount;
use SmrGalaxy;

class EditGalaxy extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/unigen/universe_create_sectors.php';

	public function __construct(
		private ?int $gameID = null,
		private ?int $galaxyID = null,
		private readonly ?string $message = null,
		private ?int $focusSectorID = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$this->gameID ??= Request::getInt('game_id');
		$this->galaxyID ??= Request::getInt('gal_on', 1);
		$this->focusSectorID ??= Request::getInt('focus_sector_id', 0);

		$galaxies = SmrGalaxy::getGameGalaxies($this->gameID);
		if (empty($galaxies)) {
			// Game was created, but no galaxies exist, so go back to
			// the galaxy generation page
			$container = new CreateGalaxies($this->gameID);
			$container->go();
		}

		$galaxy = SmrGalaxy::getGalaxy($this->gameID, $this->galaxyID);

		// Efficiently construct the caches before proceeding
		$galaxy->getSectors();
		$galaxy->getPorts();
		$galaxy->getLocations();
		$galaxy->getPlanets();

		$connectivity = round($galaxy->getConnectivity());
		$template->assign('ActualConnectivity', $connectivity);

		// Call this after all sectors have been cached in an efficient way.
		if ($this->focusSectorID == 0) {
			$mapSectors = $galaxy->getMapSectors();
		} else {
			$mapSectors = $galaxy->getMapSectors($this->focusSectorID);
			$template->assign('FocusSector', $this->focusSectorID);
		}

		$template->assign('Galaxy', $galaxy);
		$template->assign('Galaxies', $galaxies);
		$template->assign('MapSectors', $mapSectors);

		$lastSector = end($galaxies)->getEndSector();
		$template->assign('LastSector', $lastSector);

		$template->assign('Message', $this->message);

		$container = new self($this->gameID);
		$template->assign('JumpGalaxyHREF', $container->href());

		$container = new self($this->gameID, $this->galaxyID);
		$template->assign('RecenterHREF', $container->href());

		$container = new SaveProcessor($this->gameID, $this->galaxyID);
		$template->assign('SubmitChangesHREF', $container->href());

		$container = new ToggleLinkProcessor($this->gameID, $this->galaxyID);
		$container->allowAjax = true;
		$template->assign('ToggleLinkHREF', $container->href());

		$container = new DragLocationProcessor($this->gameID, $this->galaxyID);
		$container->allowAjax = true;
		$template->assign('DragLocationHREF', $container->href());

		$container = new DragPlanetProcessor($this->gameID, $this->galaxyID);
		$container->allowAjax = true;
		$template->assign('DragPlanetHREF', $container->href());

		$container = new DragPortProcessor($this->gameID, $this->galaxyID);
		$container->allowAjax = true;
		$template->assign('DragPortHREF', $container->href());

		$container = new DragWarpProcessor($this->gameID, $this->galaxyID);
		$container->allowAjax = true;
		$template->assign('DragWarpHREF', $container->href());

		$container = new EditSector($this->gameID, $this->galaxyID);
		$template->assign('ModifySectorHREF', $container->href());

		$container = new CreateLocations($this->gameID, $this->galaxyID);
		$template->assign('ModifyLocationsHREF', $container->href());

		$container = new CreatePlanets($this->gameID, $this->galaxyID);
		$template->assign('ModifyPlanetsHREF', $container->href());

		$container = new CreatePorts($this->gameID, $this->galaxyID);
		$template->assign('ModifyPortsHREF', $container->href());

		$container = new CreateWarps($this->gameID, $this->galaxyID);
		$template->assign('ModifyWarpsHREF', $container->href());

		$container = new SectorsFileDownloadProcessor($this->gameID);
		$template->assign('SMRFileHREF', $container->href());

		$container = new EditGame($this->gameID, $this->galaxyID);
		$template->assign('EditGameDetailsHREF', $container->href());

		$container = new CheckMap($this->gameID, $this->galaxyID);
		$template->assign('CheckMapHREF', $container->href());

		$container = new EditGalaxies($this->gameID, $this->galaxyID);
		$template->assign('EditGalaxyDetailsHREF', $container->href());

		$container = new ResetGalaxyProcessor($this->gameID, $this->galaxyID);
		$template->assign('ResetGalaxyHREF', $container->href());

		$template->assign('UniGen', true);
	}

}
