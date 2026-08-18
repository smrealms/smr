<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class PlayBlackjackBet extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'BlackJack';
		Menu::bar($this->locationID);

		if ($player->hasNewbieTurns()) {
			$maxBet = 100;
			$maxBetMsg = 'Since you have newbie protection, your max bet is ' . $maxBet . '.';
		} else {
			$maxBet = 10000;
			$maxBetMsg = 'Max bet is ' . $maxBet . '.';
		}

		$template->pageRenderer = fn() => PlayBlackjackBetRenderer::render(
			MaxBet: $maxBet,
			MaxBetMsg: $maxBetMsg,
			PlayHREF: new PlayBlackjackProcessor($this->locationID, 'new game')->href(),
		);
	}

}
