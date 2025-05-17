<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Galaxy;
use Smr\Game;
use Smr\Page\AccountPageProcessor;
use Smr\Sector;

class EditGalaxiesDelProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameId,
		private readonly int $deleteGalaxyId,
		private readonly EditGalaxies $returnTo,
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();

		$game = Game::getGame($this->gameId);
		if ($game->isEnabled()) {
			throw new Exception('Unexpected galaxy deletion in an enabled game!');
		}

		$galaxies = $game->getGalaxies(); // efficient cache population
		$galaxy = $galaxies[$this->deleteGalaxyId];

		// Start by shrinking the galaxy down to 0 size to handle all sectors
		$newSizes = [$this->deleteGalaxyId => ['Width' => 0, 'Height' => 0]];
		EditGalaxiesProcessor::resizeGalaxies($this->gameId, $newSizes);

		// Delete the selected galaxy
		$db->delete('game_galaxy', $galaxy->SQLID);
		unset($galaxies[$this->deleteGalaxyId]);

		// Re-index remaining galaxies
		foreach ($galaxies as $galaxyId => $galaxy) {
			if ($galaxyId > $this->deleteGalaxyId) {
				// Decrement the ID for the galaxies above the deleted galaxy
				$newGalaxyId = $galaxyId - 1;
				$data = ['galaxy_id' => $db->escapeNumber($newGalaxyId)];
				$db->update('game_galaxy', $data, $galaxy->SQLID);

				// Modify the associated sectors
				$sectors = $galaxy->getSectors();
				foreach ($sectors as $sector) {
					$sector->setGalaxyID($newGalaxyId);
				}
			}
		}

		Sector::saveSectors();
		Sector::clearCache();
		Galaxy::clearCache();

		// Adjust the galaxy the back button returns to
		if ($this->returnTo->returnTo->galaxyID >= $this->deleteGalaxyId) {
			$this->returnTo->returnTo->galaxyID -= 1;
		}
		$this->returnTo->go();
	}

}
