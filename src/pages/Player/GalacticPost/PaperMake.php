<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class PaperMake extends PlayerPage {

	public string $file = 'galactic_post_make_paper.php';

	public function build(Player $player, Template $template): void {
		$template->assign('PageTopic', 'Making A Paper');
		Menu::galacticPost();

		$container = new PaperMakeProcessor();
		$template->assign('SubmitHREF', $container->href());
	}

}
