<?php declare(strict_types=1);

namespace Smr\Page;

use AbstractSmrPlayer;
use Page;

abstract class PlayerPageProcessor extends Page {

	abstract public function build(AbstractSmrPlayer $player): never;

}
