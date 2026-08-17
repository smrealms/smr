<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Path;
use Smr\Player;
use Smr\Template;

class PlotCourseResult extends PlayerPage {

	public function __construct(
		private readonly Path $path,
	) {}

	public function build(Player $player, Template $template): void {
		$path = $this->path;
		$fullPath = implode(' - ', $path->getPath());

		$template->pageTopic = 'Plot A Course';
		Menu::navigation($player);

		$template->pageRenderer = fn() => PlotCourseResultRenderer::render($path, $fullPath);
	}

}
