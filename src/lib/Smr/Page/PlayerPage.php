<?php declare(strict_types=1);

namespace Smr\Page;

use Smr\AbstractPlayer;
use Smr\Template;

abstract class PlayerPage extends Page {

	abstract public function build(AbstractPlayer $player, Template $template): void;

}
