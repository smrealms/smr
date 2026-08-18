<?php declare(strict_types=1);

namespace Smr\Pages\Layout;

use Smr\Account;
use Smr\Player;
use Smr\Template;

readonly class SkeletonData {

	/**
	 * @param array<array{img: string, url: string, sn: string|false}> $voteLinks
	 */
	public function __construct(
		public Template $template,
		public string $timeDisplay,
		public Account $account,
		public ?Player $player,
		public ?string $gameName,
		public ?RightPanelData $rightPanelData,
		public array $voteLinks,
		public ?int $timeToNextVote,
		public string $version,
	) {}

}
