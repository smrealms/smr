<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AttackPortLoot extends PlayerPage {

	public string $file = 'port_loot.php';

	public function build(Player $player, Template $template): void {
		$template->assign('PageTopic', 'Looting The Port');
		$template->assign('ThisPort', $player->getSectorPort());
	}

}
