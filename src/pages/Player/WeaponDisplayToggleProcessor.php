<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Page\ReusableTrait;
use Smr\Session;

class WeaponDisplayToggleProcessor extends PlayerPageProcessor {

	use ReusableTrait;

	public function build(AbstractPlayer $player): never {
		$session = Session::getInstance();

		$player->setDisplayWeapons(!$player->isDisplayWeapons());
		// If this is called by ajax, we don't want to do any forwarding
		if ($session->ajax) {
			exit;
		}

		(new CurrentSector())->go();
	}

}
