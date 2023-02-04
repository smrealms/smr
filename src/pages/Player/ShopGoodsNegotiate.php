<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;
use Smr\TradeGood;
use Smr\TransactionType;

class ShopGoodsNegotiate extends PlayerPage {

	public string $file = 'shop_goods_trade.php';

	public function __construct(
		private readonly int $goodID,
		private readonly int $amount,
		private readonly int $bargainNumber,
		private readonly int $bargainPrice,
		private readonly int $offeredPrice,
		private readonly int $idealPrice,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Negotiate Price');

		// creates needed objects
		$port = $player->getSectorPort();
		// get values from request
		$good_id = $this->goodID;
		$portGood = TradeGood::get($good_id);
		$transaction = $port->getGoodTransaction($good_id);

		// Has the player failed a bargain?
		if ($this->bargainNumber > 0) {
			$template->assign('OfferToo', match ($transaction) {
				TransactionType::Sell => 'high',
				TransactionType::Buy => 'low',
			});
		}

		$template->assign('PortAction', strtolower($transaction->opposite()->value));

		$container = new ShopGoodsProcessor(
			goodID: $this->goodID,
			amount: $this->amount,
			bargainNumber: $this->bargainNumber + 1,
			bargainPrice: null,
			offeredPrice: $this->offeredPrice,
			idealPrice: $this->idealPrice,
		);
		$template->assign('BargainHREF', $container->href());

		$template->assign('BargainPrice', $this->bargainPrice);
		$template->assign('OfferedPrice', $this->offeredPrice);
		$template->assign('Transaction', $transaction);
		$template->assign('Good', $portGood);
		$template->assign('Amount', $this->amount);
		$template->assign('Port', $port);

		$container = new ShopGoods();
		$template->assign('ShopHREF', $container->href());

		$container = new CurrentSector();
		$template->assign('LeaveHREF', $container->href());
	}

}
