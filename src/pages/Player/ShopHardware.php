<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrLocation;

class ShopHardware extends PlayerPage {

	public string $file = 'shop_hardware.php';

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		if (!$player->getSector()->hasLocation($this->locationID)) {
			create_error('That location does not exist in this sector');
		}

		$location = SmrLocation::getLocation($player->getGameID(), $this->locationID);
		$template->assign('PageTopic', $location->getName());

		if ($location->isHardwareSold()) {
			$hardwareSold = $location->getHardwareSold();
			foreach ($hardwareSold as $hardwareTypeID => $hardware) {
				$container = new ShopHardwareProcessor($hardwareTypeID, $this->locationID);
				$hardwareSold[$hardwareTypeID]['HREF'] = $container->href();
			}
			$template->assign('HardwareSold', $hardwareSold);
		}
	}

}
