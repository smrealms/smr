<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class NewbieWarningProcessor extends PlayerPageProcessor {

	public function __construct() {
		$this->skipRedirect = true;
	}

	public function build(Player $player): never {
		$player->setNewbieWarning(false);
		(new NewbieWarning())->go();
	}

}
