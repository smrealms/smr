<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AllianceLeadership extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$alliance = $player->getAlliance();

		$template->pageTopic = $alliance->getAllianceDisplayName(false, true);
		Menu::alliance($player->getAllianceID());

		$members = $alliance->getMembers(includeNpc: false);
		unset($members[$alliance->getLeaderID()]); // don't show current leader
		$template->pageRenderer = fn() => AllianceLeadershipRenderer::render(
			$player,
			new AllianceLeadershipProcessor()->href(),
			$members,
		);
	}

}
