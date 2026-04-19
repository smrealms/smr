<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class Death extends PlayerPage {

	public string $file = 'death.php';

	public function build(Player $player, Template $template): void {
		$template->assign('PageTopic', 'Death');
	}

}
