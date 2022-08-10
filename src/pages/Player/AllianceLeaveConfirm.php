<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class AllianceLeaveConfirm extends PlayerPage {

	public string $file = 'alliance_leave_confirm.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$alliance = $player->getAlliance();

		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		$container = new AllianceLeaveProcessor();
		$template->assign('YesHREF', $container->href());

		$container = new CurrentSector();
		$template->assign('NoHREF', $container->href());
	}

}
