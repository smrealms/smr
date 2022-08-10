<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use AbstractSmrPlayer;
use Menu;
use Smr\Bounties;
use Smr\BountyType;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrLocation;

class Underground extends PlayerPage {

	public string $file = 'underground.php';

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		if ($player->hasGoodAlignment()) {
			create_error('You are not allowed to come in here!');
		}

		if (!$player->getSector()->hasLocation($this->locationID)) {
			create_error('That location does not exist in this sector');
		}
		$location = SmrLocation::getLocation($player->getGameID(), $this->locationID);
		if (!$location->isUG()) {
			create_error('There is no underground here.');
		}

		$template->assign('PageTopic', $location->getName());

		Menu::headquarters($this->locationID);

		$template->assign('AllBounties', Bounties::getMostWanted(BountyType::UG));
		$template->assign('MyBounties', $player->getClaimableBounties(BountyType::UG));

		if ($player->hasNeutralAlignment()) {
			$container = new GovernmentProcessor($this->locationID);
			$template->assign('JoinHREF', $container->href());
		}
	}

}
