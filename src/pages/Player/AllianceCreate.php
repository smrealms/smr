<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;

class AllianceCreate extends PlayerPage {

	public string $file = 'alliance_create.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Create Alliance');

		$container = new AllianceCreateProcessor();
		$template->assign('CreateHREF', $container->href());
	}

}
