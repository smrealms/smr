<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;

class NewbieWarningProcessor extends PlayerPageProcessor {

	public function __construct() {
		$this->skipRedirect = true;
	}

	public function build(AbstractPlayer $player): never {
		$player->setNewbieWarning(false);
		(new NewbieWarning())->go();
	}

}
