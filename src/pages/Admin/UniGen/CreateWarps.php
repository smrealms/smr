<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Database;
use Smr\Galaxy;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Sector;
use Smr\Template;

class CreateWarps extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/unigen/universe_create_warps.php';

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID,
		private readonly ?string $message = null
	) {}

	public function build(Account $account, Template $template): void {
		$db = Database::getInstance();

		$template->assign('Message', $this->message);

		$galaxies = Galaxy::getGameGalaxies($this->gameID);
		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);

		$template->assign('PageTopic', 'Warps for Galaxy : ' . $galaxy->getDisplayName() . ' (' . $galaxy->getGalaxyID() . ')');

		// Initialize warps array
		$warps = [];
		foreach ($galaxies as $gal1) {
			$warps[$gal1->getGalaxyID()] = [];
			foreach ($galaxies as $gal2) {
				$warps[$gal1->getGalaxyID()][$gal2->getGalaxyID()] = 0;
			}
		}

		//get totals
		$dbResult = $db->read('SELECT sector_id, warp FROM sector WHERE warp != 0 AND game_id=' . $db->escapeNumber($this->gameID));
		foreach ($dbResult->records() as $dbRecord) {
			$warp1 = Sector::getSector($this->gameID, $dbRecord->getInt('sector_id'));
			$warp2 = Sector::getSector($this->gameID, $dbRecord->getInt('warp'));
			if ($warp1->getGalaxyID() == $warp2->getGalaxyID()) {
				// For warps within the same galaxy, even though there will be two
				// sectors with warps, we still consider this as "one warp" (pair).
				// Since we're looping over all sectors, we'll hit this twice for each
				// same-galaxy warp pair, so only add 0.5 to avoid double counting.
				$warps[$warp1->getGalaxyID()][$warp2->getGalaxyID()] += 0.5;
			} else {
				$warps[$warp1->getGalaxyID()][$warp2->getGalaxyID()]++;
			}
		}

		// Get links to other pages
		$galLinks = [];
		foreach ($galaxies as $gal) {
			$container = new self($this->gameID, $gal->getGalaxyID());
			$galLinks[$gal->getGalaxyID()] = $container->href();
		}
		$template->assign('GalLinks', $galLinks);

		$container = new SaveProcessor($this->gameID, $this->galaxyID);
		$template->assign('SubmitHREF', $container->href());

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$template->assign('CancelHREF', $container->href());

		$template->assign('Galaxy', $galaxy);
		$template->assign('Galaxies', $galaxies);
		$template->assign('Warps', $warps);
	}

}
