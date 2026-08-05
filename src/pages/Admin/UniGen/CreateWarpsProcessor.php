<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Galaxy;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use Smr\Sector;

class CreateWarpsProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID,
		private readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account): never {
		//get all warp info from all gals, some need to be removed, some need to be added
		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);
		$galSectors = $galaxy->getSectors();
		//get totals
		foreach ($galSectors as $galSector) {
			if ($galSector->hasWarp()) {
				$galSector->removeWarp();
			}
		}
		//iterate over all the galaxies
		$galaxies = Galaxy::getGameGalaxies($this->gameID);
		foreach ($galaxies as $eachGalaxy) {
			//do we have a warp to this gal?
			if (Request::has('warp' . $eachGalaxy->getGalaxyID())) {
				// Sanity check the number
				$numWarps = Request::getInt('warp' . $eachGalaxy->getGalaxyID());
				if ($numWarps > 10) {
					create_error('Specify no more than 10 warps between two galaxies!');
				}
				//iterate for each warp to this gal
				for ($i = 1; $i <= $numWarps; $i++) {
					//only 1 warp per sector
					$galSector = findValidSector(
						$galSectors,
						fn(Sector $sector): bool => !$sector->hasWarp() && !$sector->offersFederalProtection(),
					);
					//get other side
					//make sure it does not go to itself
					$otherSector = findValidSector(
						$eachGalaxy->getSectors(),
						fn(Sector $sector): bool => !$sector->hasWarp() && !$sector->offersFederalProtection() && !$sector->equals($galSector),
					);
					$galSector->setWarp($otherSector);
				}
			}
		}
		Sector::saveSectors();
		$message = '<span class="green">Success</span> : added warps.';
		(new CreateWarps($this->gameID, $this->galaxyID, $this->returnTo, $message))->go();

	}

}
