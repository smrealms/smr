<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Galaxy;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use Smr\Sector;

class CreateGalaxiesProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $numGalaxies,
	) {}

	public function build(Account $account): never {
		for ($i = 1; $i <= $this->numGalaxies; $i++) {
			$galaxy = Galaxy::createGalaxy($this->gameID, $i);
			$galaxy->setName(Request::get('gal' . $i));
			$galaxy->setWidth(Request::getInt('width' . $i));
			$galaxy->setHeight(Request::getInt('height' . $i));
			$galaxy->setGalaxyType(Request::get('type' . $i));
			$galaxy->setMaxForceTime(IFloor(Request::getFloat('forces' . $i) * 3600));
		}
		// Workaround for Galaxy::getStartSector depending on all other galaxies
		Galaxy::saveGalaxies();
		$galaxies = Galaxy::getGameGalaxies($this->gameID, true);
		foreach ($galaxies as $galaxy) {
			$galaxy->generateSectors();
		}
		Sector::saveSectors();

		$message = '<span class="green">Success</span> : Succesfully created galaxies.';
		$container = new EditGalaxy($this->gameID, message: $message);
		$container->go();
	}

}
