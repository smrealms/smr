<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;
use Smr\TradeGood;
use Smr\TransactionType;

class ShopGoodsNegotiate extends PlayerPage {

	public function __construct(
		private readonly int $goodID,
		private readonly int $amount,
		private readonly int $bargainNumber,
		private readonly int $bargainPrice,
		private readonly int $offeredPrice,
		private readonly int $idealPrice,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Negotiate Price';

		// creates needed objects
		$port = $player->getSectorPort();
		// get values from request
		$good_id = $this->goodID;
		$portGood = TradeGood::get($good_id);
		$transaction = $port->getGoodTransaction($good_id);

		// Has the player failed a bargain?
		if ($this->bargainNumber > 0) {
			$offerToo = match ($transaction) {
				TransactionType::Sell => 'high',
				TransactionType::Buy => 'low',
			};
		} else {
			$offerToo = null;
		}

		$bargainHREF = new ShopGoodsProcessor(
			goodID: $this->goodID,
			amount: $this->amount,
			bargainNumber: $this->bargainNumber + 1,
			offeredPrice: $this->offeredPrice,
			idealPrice: $this->idealPrice,
		)->href();

		$template->pageRenderer = fn() => ShopGoodsNegotiateRenderer::render(
			OfferToo: $offerToo,
			PortAction: strtolower($transaction->opposite()->value),
			BargainHREF: $bargainHREF,
			BargainPrice: $this->bargainPrice,
			OfferedPrice: $this->offeredPrice,
			Good: $portGood,
			Amount: $this->amount,
			Port: $port,
			ShopHREF: new ShopGoods()->href(),
			LeaveHREF: new CurrentSector()->href(),
			ThisPlayer: $player,
		);
	}

}
