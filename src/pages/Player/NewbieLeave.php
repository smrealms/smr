<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class NewbieLeave extends PlayerPage {

	use ReusableTrait;

	public string $file = 'leave_newbie.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		if (!$player->getGame()->hasStarted()) {
			create_error('You cannot leave newbie protection before the game begins!');
		}

		$template->assign('PageTopic', 'Leave Newbie Protection');
	}

}
