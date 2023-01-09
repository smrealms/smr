<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class UserRankingView extends PlayerPage {

	use ReusableTrait;

	public string $file = 'rankings_view.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Extended User Rankings');
		Menu::trader();
	}

}
