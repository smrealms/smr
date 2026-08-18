<?php declare(strict_types=1);

namespace Smr\Pages\Layout;

use Smr\Player;

readonly class RightPanelData {

	/**
	 * @param array<array{href: string, num: int, alt: string, img: string}> $unreadMessages
	 */
	public function __construct(
		public Player $player,
		public bool $underAttack,
		public array $unreadMessages,
		public string $playerNameLink,
		public string $hardwareLink,
		public string $forcesDropLink,
		public string $cargoJettisonLink,
		public string $weaponReorderLink,
		public ?string $dropMineLink,
		public ?string $dropCDLink,
		public ?string $dropSDLink,
	) {}

}
