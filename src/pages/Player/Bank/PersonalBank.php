<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class PersonalBank extends PlayerPage {

	public string $file = 'bank_personal.php';

	public function build(AbstractPlayer $player, Template $template): void {
		// is account validated?
		if (!$player->getAccount()->isValidated()) {
			create_error('You are not validated so you cannot use banks.');
		}

		$template->assign('PageTopic', 'Bank');

		Menu::bank();

		$container = new PersonalBankProcessor();
		$template->assign('ProcessingHREF', $container->href());
	}

}
