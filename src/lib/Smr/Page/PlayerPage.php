<?php declare(strict_types=1);

namespace Smr\Page;

use AbstractSmrPlayer;
use Smr\Template;

abstract class PlayerPage extends Page {

	abstract public function build(AbstractSmrPlayer $player, Template $template): void;

}
