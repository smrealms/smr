<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AllianceManageNpcs extends PlayerPage {

	public string $file = 'alliance_manage_npcs.php';

	public function build(Player $player, Template $template): void {
		$alliance = $player->getAlliance();

		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		$npcs = [];
		foreach ($alliance->getNpcs() as $npc) {
			$npcs[] = [
				'player' => $npc,
				'dismissHref' => (new AllianceManageNpcsDismissProcessor($npc->getAccountID()))->href(),
			];
		}
		$template->assign('Npcs', $npcs);
	}

}
