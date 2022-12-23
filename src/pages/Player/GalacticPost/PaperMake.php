<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use AbstractSmrPlayer;
use Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class PaperMake extends PlayerPage {

	public string $file = 'galactic_post_make_paper.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Making A Paper');
		Menu::galacticPost();

		$container = new PaperMakeProcessor();
		$template->assign('SubmitHREF', $container->href());
	}

}
