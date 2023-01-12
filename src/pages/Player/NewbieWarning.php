<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;

class NewbieWarning extends PlayerPage {

	public string $file = 'newbie_warning.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Warning!');
	}

}
