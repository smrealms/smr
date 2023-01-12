<?php declare(strict_types=1);

namespace Smr\Page;

use Smr\AbstractPlayer;

abstract class PlayerPageProcessor extends Page {

	abstract public function build(AbstractPlayer $player): never;

}
