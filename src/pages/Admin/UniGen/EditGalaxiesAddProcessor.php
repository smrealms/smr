<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Galaxy;
use Smr\Game;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use Smr\Sector;

class EditGalaxiesAddProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly EditGalaxies $returnTo,
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();

		$insertGalaxyId = Request::getInt('insert_galaxy_id');

		$game = Game::getGame($this->gameID);
		if ($game->isEnabled()) {
			throw new Exception('Unexpected galaxy insertion in an enabled game!');
		}
		if ($insertGalaxyId > $game->getNumberOfGalaxies() + 1) {
			throw new Exception('Cannot insert galaxy ID: ' . $insertGalaxyId);
		}

		// Make room for the new galaxy, working in reverse galaxy ID order
		$galaxies = $game->getGalaxies();
		krsort($galaxies);
		foreach ($galaxies as $galaxyId => $galaxy) {
			if ($galaxyId >= $insertGalaxyId) {
				// Increment the galaxy ID
				$newGalaxyId = $galaxyId + 1;
				$data = ['galaxy_id' => $db->escapeNumber($newGalaxyId)];
				$db->update('game_galaxy', $data, $galaxy->SQLID);

				// Modify the associated sectors
				$sectors = $galaxy->getSectors();
				foreach ($sectors as $sector) {
					$sector->setGalaxyID($newGalaxyId);
				}
			}
		}
		// Save modified sectors, then reset cache since primary IDs changed
		Sector::saveSectors();
		Sector::clearCache();
		Galaxy::clearCache();

		// Add a new empty galaxy
		$galaxy = Galaxy::createGalaxy($this->gameID, $insertGalaxyId);
		$galaxy->setName('NEW GALAXY');
		$galaxy->setWidth(0);
		$galaxy->setHeight(0);
		$galaxy->setGalaxyType(Galaxy::TYPE_NEUTRAL);
		$galaxy->setMaxForceTime(0);
		$galaxy->save();

		// Adjust the galaxy the back button returns to
		if ($this->returnTo->returnTo->galaxyID >= $insertGalaxyId) {
			$this->returnTo->returnTo->galaxyID += 1;
		}
		$this->returnTo->go();
	}

}
