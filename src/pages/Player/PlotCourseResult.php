<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Path;
use Smr\Template;

class PlotCourseResult extends PlayerPage {

	public string $file = 'course_plot_result.php';

	public function __construct(
		private readonly Path $path,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$path = $this->path;
		$fullPath = implode(' - ', $path->getPath());

		$template->assign('PageTopic', 'Plot A Course');
		Menu::navigation($player);

		$template->assign('Path', $path);
		$template->assign('FullPath', $fullPath);
	}

}
