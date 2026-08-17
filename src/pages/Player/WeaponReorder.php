<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class WeaponReorder extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Weapon Reorder';

		$template->pageRenderer = fn() => WeaponReorderRenderer::render(
			template: $template,
			ThisShip: $player->getShip(),
		);
	}

}
