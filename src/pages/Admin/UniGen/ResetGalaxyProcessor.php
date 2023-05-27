<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Galaxy;
use Smr\Page\AccountPageProcessor;
use Smr\Sector;

class ResetGalaxyProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID,
	) {}

	public function build(Account $account): never {
		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);

		// Efficiently construct the caches before proceeding
		$galaxy->getPorts();
		$galaxy->getPlanets();
		$galaxy->getLocations();

		$galaxy->setConnectivity(100);

		// Remove all ports, planets, locations, and warps
		foreach ($galaxy->getSectors() as $galSector) {
			$galSector->removeAllFixtures();
		}

		Sector::saveSectors();

		$message = '<span class="green">Success</span> : reset galaxy.';
		$container = new EditGalaxy($this->gameID, $this->galaxyID, $message);
		$container->go();
	}

}
