<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\PortPayoutType;

class AttackPortPayoutProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly PortPayoutType $payoutType,
	) {}

	public function build(AbstractPlayer $player): never {
		$port = $player->getSectorPort();
		if (!$port->exists()) {
			create_error('The port no longer exists!');
		}
		if (!$port->isBusted()) {
			create_error('The port is no longer defenceless!');
		}

		$payoutType = $this->payoutType;

		$credits = match ($payoutType) {
			PortPayoutType::Raze => $port->razePort($player),
			PortPayoutType::Loot => $port->lootPort($player),
			PortPayoutType::Claim => $port->claimPort($player),
			PortPayoutType::Destroy => $port->destroyPort($player),
		};
		$player->log(LOG_TYPE_TRADING, 'Player Triggers Payout: ' . $payoutType->name);
		$port->update();

		$msg = match ($payoutType) {
			PortPayoutType::Raze => 'You have razed the port and salvaged <span class="creds">' . number_format($credits) . '</span> credits from the wreckage.',
			PortPayoutType::Loot => 'You have looted <span class="creds">' . number_format($credits) . '</span> credits from the port.',
			PortPayoutType::Claim => 'You expel the port owners and claim the port for your race, skimming <span class="creds">' . number_format($credits) . '</span> credits from the coffers for your efforts.',
			PortPayoutType::Destroy => 'The busted port drifts before you, inert and almost peaceful now that the defences have been neutralised. You notice a large breach in its central hull, and see its primary power source through disfigured metal teeth. Either out of spite or cold calculation, you take aim at the exposed nuclear core and pull the trigger. It is instantly vaporized. Nothing remains of the port and its inhabitants apart from a scintillating cloud of radioactive dust. You monster.',
		};

		$container = new CurrentSector(message: $msg);
		$container->go();
	}

}
