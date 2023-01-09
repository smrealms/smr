<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\AbstractPlayer;
use Smr\Bounty;
use Smr\BountyType;
use Smr\Location;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class Underground extends PlayerPage {

	public string $file = 'underground.php';

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
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

		$template->assign('PageTopic', $location->getName());

		Menu::headquarters($this->locationID);

		$template->assign('AllBounties', Bounty::getMostWanted(BountyType::UG, $player->getGameID()));
		$template->assign('MyBounties', $player->getClaimableBounties(BountyType::UG));

		if ($player->hasNeutralAlignment()) {
			$container = new GovernmentProcessor($this->locationID);
			$template->assign('JoinHREF', $container->href());
		}
	}

}
