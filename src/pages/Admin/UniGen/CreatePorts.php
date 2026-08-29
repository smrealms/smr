<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Galaxy;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Port;
use Smr\Race;
use Smr\Request;
use Smr\Template;

class CreatePorts extends AccountPage {

	use ReusableTrait;
	public function __construct(
		private readonly int $gameID,
		private readonly EditGalaxy $returnTo,
		private ?int $galaxyID = null,
	) {}

	public function build(Account $account, Template $template): void {
		$this->galaxyID ??= Request::getInt('gal_on');

		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);

		// initialize totals
		$totalPorts = array_fill(1, Port::getMaxLevelByGame($this->gameID), 0);
		$totalRaces = array_fill_keys(Race::getAllIDs(), 0);
		$racePercents = $totalRaces;

		foreach ($galaxy->getSectors() as $galSector) {
			$port = $galSector->getPortOrNull();
			if ($port !== null) {
				$totalRaces[$port->getRaceID()]++;
				$totalPorts[$port->getLevel()]++;
			}
		}
		$total = array_sum($totalPorts);

		if ($total > 0) {
			foreach ($totalRaces as $raceID => $totalRace) {
				$racePercents[$raceID] = round($totalRace / $total * 100);
			}
		}

		$template->pageRenderer = fn() => CreatePortsRenderer::render(
			Galaxies: Galaxy::getGameGalaxies($this->gameID),
			JumpGalaxyHREF: new self($this->gameID, $this->returnTo)->href(),
			Galaxy: $galaxy,
			RacePercents: $racePercents,
			TotalPercent: array_sum($racePercents),
			TotalPorts: $totalPorts,
			Total: array_sum($totalPorts),
			CreateHREF: new CreatePortsProcessor($this->gameID, $this->galaxyID, $this->returnTo)->href(),
			CancelHREF: $this->returnTo->href(),
		);
	}

}
