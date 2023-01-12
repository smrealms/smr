<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;

class AttackPortLoot extends PlayerPage {

	public string $file = 'port_loot.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Looting The Port');
		$template->assign('ThisPort', $player->getSectorPort());
	}

}
