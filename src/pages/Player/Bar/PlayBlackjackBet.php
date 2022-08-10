<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use AbstractSmrPlayer;
use Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class PlayBlackjackBet extends PlayerPage {

	public string $file = 'bar_gambling_bet.php';

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'BlackJack');
		Menu::bar($this->locationID);

		if ($player->hasNewbieTurns()) {
			$maxBet = 100;
			$maxBetMsg = 'Since you have newbie protection, your max bet is ' . $maxBet . '.';
		} else {
			$maxBet = 10000;
			$maxBetMsg = 'Max bet is ' . $maxBet . '.';
		}
		$template->assign('MaxBet', $maxBet);
		$template->assign('MaxBetMsg', $maxBetMsg);

		$container = new PlayBlackjackProcessor($this->locationID, 'new game');
		$template->assign('PlayHREF', $container->href());
	}

}
