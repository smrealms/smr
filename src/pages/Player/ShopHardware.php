<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Location;
use Smr\Page\PlayerPage;
use Smr\Template;

class ShopHardware extends PlayerPage {

	public string $file = 'shop_hardware.php';

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		if (!$player->getSector()->hasLocation($this->locationID)) {
			create_error('That location does not exist in this sector');
		}

		$location = Location::getLocation($player->getGameID(), $this->locationID);
		$template->assign('PageTopic', $location->getName());

		if ($location->isHardwareSold()) {
			$hardwareSold = [];
			foreach ($location->getHardwareSold() as $hardwareTypeID => $hardwareType) {
				$container = new ShopHardwareProcessor($hardwareTypeID, $this->locationID);
				$hardwareSold[$hardwareTypeID] = [
					'HREF' => $container->href(),
					'Cost' => $hardwareType->cost,
					'Name' => $hardwareType->name,
				];
			}
			$template->assign('HardwareSold', $hardwareSold);
		}
	}

}
