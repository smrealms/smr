<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class WeaponReorder extends PlayerPage {

	use ReusableTrait;

	public string $file = 'weapon_reorder.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Weapon Reorder');
	}

}
