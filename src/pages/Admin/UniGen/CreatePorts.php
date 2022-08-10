<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Race;
use Smr\Request;
use Smr\Template;
use SmrAccount;
use SmrGalaxy;
use SmrPort;

class CreatePorts extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/unigen/universe_create_ports.php';

	public function __construct(
		private readonly int $gameID,
		private ?int $galaxyID = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$this->galaxyID ??= Request::getInt('gal_on');
		$template->assign('Galaxies', SmrGalaxy::getGameGalaxies($this->gameID));

		$container = new self($this->gameID);
		$template->assign('JumpGalaxyHREF', $container->href());

		$galaxy = SmrGalaxy::getGalaxy($this->gameID, $this->galaxyID);
		$template->assign('Galaxy', $galaxy);

		// initialize totals
		$totalPorts = array_fill(1, SmrPort::getMaxLevelByGame($this->gameID), 0);
		$totalRaces = array_fill_keys(Race::getAllIDs(), 0);
		$racePercents = $totalRaces;

		foreach ($galaxy->getSectors() as $galSector) {
			if ($galSector->hasPort()) {
				$totalRaces[$galSector->getPort()->getRaceID()]++;
				$totalPorts[$galSector->getPort()->getLevel()]++;
			}
		}
		$total = array_sum($totalPorts);

		if ($total > 0) {
			foreach ($totalRaces as $raceID => $totalRace) {
				$racePercents[$raceID] = round($totalRace / $total * 100);
			}
		}
		$template->assign('RacePercents', $racePercents);
		$template->assign('TotalPercent', array_sum($racePercents));

		$container = new SaveProcessor($this->gameID, $this->galaxyID);
		$template->assign('CreateHREF', $container->href());

		$template->assign('TotalPorts', $totalPorts);
		$template->assign('Total', array_sum($totalPorts));
	}

}
