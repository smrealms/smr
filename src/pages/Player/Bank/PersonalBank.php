<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class PersonalBank extends PlayerPage {

	public function build(Player $player, Template $template): void {
		// is account validated?
		if (!$player->getAccount()->isValidated()) {
			create_error('You are not validated so you cannot use banks.');
		}

		$template->pageTopic = 'Bank';

		Menu::bank();

		$container = new PersonalBankProcessor();

		$template->pageRenderer = fn() => PersonalBankRenderer::render(
			ProcessingPage: $container,
			ThisPlayer: $player,
		);
	}

}
