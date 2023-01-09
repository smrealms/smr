<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\AbstractPlayer;
use Smr\Bounty;
use Smr\BountyType;
use Smr\Globals;
use Smr\Location;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Race;
use Smr\Template;

class Government extends PlayerPage {

	public string $file = 'government.php';

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		// check if our alignment is high enough
		if ($player->hasEvilAlignment()) {
			create_error('You are not allowed to enter our Government HQ!');
		}
		if (!$player->getSector()->hasLocation($this->locationID)) {
			create_error('That location does not exist in this sector');
		}
		$location = Location::getLocation($player->getGameID(), $this->locationID);
		if (!$location->isHQ()) {
			create_error('There is no headquarters. Obviously.');
		}
		$raceID = $location->getRaceID();

		// are we at war?
		if ($player->getRelation($raceID) <= RELATIONS_WAR) {
			create_error('We are at WAR with your race! Get outta here before I call the guards!');
		}

		$template->assign('PageTopic', $location->getName());

		// header menu
		Menu::headquarters($this->locationID);

		$warRaces = [];
		if ($raceID != RACE_NEUTRAL) {
			$raceRelations = Globals::getRaceRelations($player->getGameID(), $raceID);
			foreach ($raceRelations as $otherRaceID => $relation) {
				if ($relation <= RELATIONS_WAR) {
					$warRaces[] = Race::getName($otherRaceID);
				}
			}
		}
		$template->assign('WarRaces', $warRaces);

		$template->assign('AllBounties', Bounty::getMostWanted(BountyType::HQ, $player->getGameID()));
		$template->assign('MyBounties', $player->getClaimableBounties(BountyType::HQ));

		if ($player->hasNeutralAlignment()) {
			$container = new GovernmentProcessor($this->locationID);
			$template->assign('JoinHREF', $container->href());
		}
	}

}
