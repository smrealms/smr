<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class AllianceLeadership extends PlayerPage {

	public string $file = 'alliance_leadership.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$alliance = $player->getAlliance();

		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($player->getAllianceID());

		$container = new AllianceLeadershipProcessor();
		$template->assign('HandoverHREF', $container->href());

		$template->assign('AlliancePlayers', $alliance->getMembers());
	}

}
