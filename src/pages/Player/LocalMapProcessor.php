<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;

class LocalMapProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly string $zoomDir
	) {}

	public function build(AbstractSmrPlayer $player): never {
		if ($this->zoomDir == 'Shrink') {
			$player->decreaseZoom(1);
		} elseif ($this->zoomDir == 'Expand') {
			$player->increaseZoom(1);
		}

		(new LocalMap())->go();
	}

}
