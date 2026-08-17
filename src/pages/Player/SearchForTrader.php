<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class SearchForTrader extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private readonly bool $emptyResult = false,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Search For Trader';

		$template->pageRenderer = fn() => SearchForTraderRenderer::render(
			TraderSearchHREF: new SearchForTraderResult()->href(),
			EmptyResult: $this->emptyResult,
		);
	}

}
