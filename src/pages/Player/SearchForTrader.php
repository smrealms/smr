<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class SearchForTrader extends PlayerPage {

	use ReusableTrait;

	public string $file = 'trader_search.php';

	public function __construct(
		private readonly bool $emptyResult = false
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Search For Trader');
		$template->assign('TraderSearchHREF', (new SearchForTraderResult())->href());

		$template->assign('EmptyResult', $this->emptyResult);
	}

}
