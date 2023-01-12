<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;

class BuyShipNamePreviewProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly string $shipName,
		private readonly int $cost
	) {}

	public function build(AbstractPlayer $player): never {
		$account = $player->getAccount();

		$player->setCustomShipName($this->shipName);
		$account->decreaseTotalSmrCredits($this->cost);

		$message = 'Thanks for your purchase! Your ship is ready!<br /><small>If your ship is found to use HTML inappropriately you may be banned. If your ship does contain inappropriate HTML, please notify an admin ASAP.</small>';
		$container = new CurrentSector(message: $message);
		$container->go();
	}

}
