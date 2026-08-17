<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AllianceCreate extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Create Alliance';

		$template->pageRenderer = fn() => AllianceCreateRenderer::render(
			new AllianceCreateProcessor()->href(),
		);
	}

}
