<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrSector;

class SectorJumpCalculate extends PlayerPage {

	public string $file = 'sector_jump_calculate.php';

	public function __construct(
		private readonly int $targetSectorID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Jump Drive');
		Menu::navigation($player);

		$targetSector = SmrSector::getSector($player->getGameID(), $this->targetSectorID);
		$jumpInfo = $player->getJumpInfo($targetSector);

		$template->assign('Target', $targetSector->getSectorID());
		$template->assign('TurnCost', $jumpInfo['turn_cost']);
		$template->assign('MaxMisjump', $jumpInfo['max_misjump']);

		$container = new SectorJumpProcessor($targetSector->getSectorID());
		$template->assign('JumpProcessingHREF', $container->href());
	}

}
