<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AnonBankCreate extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Create Anonymous Account';
		Menu::bank();

		$template->pageRenderer = fn() => AnonBankCreateRenderer::render(
			new AnonBankCreateProcessor()->href(),
		);
	}

}
