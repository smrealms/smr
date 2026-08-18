<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class PaperMake extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Making A Paper';
		Menu::galacticPost();

		$template->pageRenderer = fn() => PaperMakeRenderer::render(new PaperMakeProcessor()->href());
	}

}
