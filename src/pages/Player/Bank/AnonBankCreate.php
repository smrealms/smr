<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class AnonBankCreate extends PlayerPage {

	public string $file = 'bank_anon_create.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Create Anonymous Account');
		Menu::bank();

		$container = new AnonBankCreateProcessor();
		$template->assign('CreateHREF', $container->href());
	}

}
