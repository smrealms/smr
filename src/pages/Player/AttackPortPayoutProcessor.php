<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\PortPayoutType;

class AttackPortPayoutProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly PortPayoutType $payoutType
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$port = $player->getSectorPort();
		if (!$port->isDestroyed()) {
			create_error('The port is no longer defenceless!');
		}

		$payoutType = $this->payoutType;

		$credits = match ($payoutType) {
			PortPayoutType::Raze => $port->razePort($player),
			PortPayoutType::Loot => $port->lootPort($player),
		};
		$player->log(LOG_TYPE_TRADING, 'Player Triggers Payout: ' . $payoutType->name);
		$port->update();
		$msg = 'You have taken <span class="creds">' . number_format($credits) . '</span> from the port.';
		$container = new CurrentSector(message: $msg);
		$container->go();
	}

}
