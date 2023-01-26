<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Exceptions\PathNotFound;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Sector;
use Smr\Template;

class SectorJumpCalculate extends PlayerPage {

	public string $file = 'sector_jump_calculate.php';

	public function __construct(
		private readonly int $targetSectorID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Jump Drive');
		Menu::navigation($player);

		$targetSector = Sector::getSector($player->getGameID(), $this->targetSectorID);
		try {
			$jumpInfo = $player->getJumpInfo($targetSector);
		} catch (PathNotFound) {
			create_error('Unable to plot from ' . $player->getSectorID() . ' to ' . $targetSector->getSectorID());
		}

		$template->assign('Target', $targetSector->getSectorID());
		$template->assign('TurnCost', $jumpInfo['turn_cost']);
		$template->assign('MaxMisjump', $jumpInfo['max_misjump']);

		$container = new SectorJumpProcessor($targetSector->getSectorID());
		$template->assign('JumpProcessingHREF', $container->href());
	}

}
