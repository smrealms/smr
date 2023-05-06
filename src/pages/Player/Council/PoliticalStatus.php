<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\AbstractPlayer;
use Smr\Globals;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Race;
use Smr\Template;

class PoliticalStatus extends PlayerPage {

	use ReusableTrait;

	public string $file = 'council_politics.php';

	public function __construct(
		private readonly int $raceID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$raceID = $this->raceID;

		$template->assign('PageTopic', 'Ruling Council Of ' . Race::getName($raceID));

		// echo menu
		Menu::council($raceID);

		$raceRelations = Globals::getRaceRelations($player->getGameID(), $raceID);

		$peaceRaces = [];
		$neutralRaces = [];
		$warRaces = [];
		foreach (Race::getPlayableIDs() as $otherRaceID) {
			if ($raceID !== $otherRaceID) {
				if ($raceRelations[$otherRaceID] >= RELATIONS_PEACE) {
					$peaceRaces[] = $otherRaceID;
				} elseif ($raceRelations[$otherRaceID] <= RELATIONS_WAR) {
					$warRaces[] = $otherRaceID;
				} else {
					$neutralRaces[] = $otherRaceID;
				}
			}
		}

		$template->assign('PeaceRaces', $peaceRaces);
		$template->assign('NeutralRaces', $neutralRaces);
		$template->assign('WarRaces', $warRaces);
	}

}
