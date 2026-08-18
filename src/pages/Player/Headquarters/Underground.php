<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\Bounty;
use Smr\BountyType;
use Smr\Location;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class Underground extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(Player $player, Template $template): void {
		if ($player->hasGoodAlignment()) {
			create_error('You are not allowed to come in here!');
		}

		if (!$player->getSector()->hasLocation($this->locationID)) {
			create_error('That location does not exist in this sector');
		}
		$location = Location::getLocation($player->getGameID(), $this->locationID);
		if (!$location->isUG()) {
			create_error('There is no underground here.');
		}

		$template->pageTopic = $location->getName();

		Menu::headquarters($this->locationID);

		$joinHREF = $player->hasNeutralAlignment() ?
			new GovernmentProcessor($this->locationID)->href() : null;

		$template->pageRenderer = fn() => UndergroundRenderer::render(
			AllBounties: Bounty::getMostWanted(BountyType::UG, $player->getGameID()),
			MyBounties: $player->getClaimableBounties(BountyType::UG),
			JoinHREF: $joinHREF,
		);
	}

}
