<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Exceptions\PathNotFound;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Sector;
use Smr\Template;

class SectorJumpCalculate extends PlayerPage {

	public function __construct(
		private readonly int $targetSectorID,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Jump Drive';
		Menu::navigation($player);

		$targetSector = Sector::getSector($player->getGameID(), $this->targetSectorID);
		try {
			$jumpInfo = $player->getJumpInfo($targetSector);
		} catch (PathNotFound) {
			create_error('Unable to plot from ' . $player->getSectorID() . ' to ' . $targetSector->getSectorID());
		}

		$template->pageRenderer = fn() => SectorJumpCalculateRenderer::render(
			Target: $targetSector->getSectorID(),
			TurnCost: $jumpInfo['turn_cost'],
			MaxMisjump: $jumpInfo['max_misjump'],
			JumpProcessingHREF: new SectorJumpProcessor($targetSector->getSectorID())->href(),
		);
	}

}
