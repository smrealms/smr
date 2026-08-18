<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class NewbieLeave extends PlayerPage {

	use ReusableTrait;

	public function build(Player $player, Template $template): void {
		if (!$player->getGame()->hasStarted()) {
			create_error('You cannot leave newbie protection before the game begins!');
		}

		$template->pageRenderer = fn() => NewbieLeaveRenderer::render(
			CancelHREF: new CurrentSector()->href(),
			ConfirmHREF: $player->getLeaveNewbieProtectionHREF(),
		);

		$template->pageTopic = 'Leave Newbie Protection';
	}

}
