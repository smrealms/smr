<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class AllianceLeadership extends PlayerPage {

	public string $file = 'alliance_leadership.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$alliance = $player->getAlliance();

		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($player->getAllianceID());

		$container = new AllianceLeadershipProcessor();
		$template->assign('HandoverHREF', $container->href());

		$template->assign('AlliancePlayers', $alliance->getMembers());
	}

}
