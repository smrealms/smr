<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;

class NewbieWarningProcessor extends PlayerPageProcessor {

	public function __construct() {
		$this->skipRedirect = true;
	}

	public function build(AbstractSmrPlayer $player): never {
		$player->setNewbieWarning(false);
		(new NewbieWarning())->go();
	}

}
