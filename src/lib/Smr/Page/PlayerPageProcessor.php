<?php declare(strict_types=1);

namespace Smr\Page;

use Smr\Player;

abstract class PlayerPageProcessor extends Page {

	abstract public function build(Player $player): never;

}
