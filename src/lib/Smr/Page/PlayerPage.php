<?php declare(strict_types=1);

namespace Smr\Page;

use Smr\Player;
use Smr\Template;

abstract class PlayerPage extends Page {

	abstract public function build(Player $player, Template $template): void;

}
