<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Port;
use Smr\Request;
use Smr\TradeGood;
use Smr\TransactionType;

class ShopGoodsProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $goodID,
		private readonly ?int $amount = null,
		private readonly int $bargainNumber = 0,
		private readonly ?int $bargainPrice = null, // only for NPC
		private readonly ?int $offeredPrice = null,
		private readonly ?int $idealPrice = null,
	) {}

	public function build(AbstractPlayer $player): never {
		$ship = $player->getShip();
		$sector = $player->getSector();

		$amount = $this->amount ?? Request::getInt('amount');
		// no negative amounts are allowed
		if ($amount <= 0) {
			create_error('You must enter an amount > 0!');
		}

		$good_id = $this->goodID;
		$portGood = TradeGood::get($good_id);
		$good_name = $portGood->name;

		// do we have enough turns?
		if ($player->getTurns() === 0) {
			create_error('You don\'t have enough turns to trade.');
		}

		// get rid of those bugs when we die...there is no port at the home sector
		if (!$sector->hasPort()) {
			create_error('I can\'t see a port in this sector. Can you?');
		}
		$port = $sector->getPort();

		// check if the player has the right relations to trade at the current port
		if ($player->getRelation($port->getRaceID()) < RELATIONS_WAR) {
			create_error('This port refuses to trade with you because you are at <span class="big bold red">WAR!</span>');
		}

		// does the port actually buy or sell this good?
		if (!$port->hasGood($good_id)) {
			create_error('I don\'t trade in that good.');
		}

		// check if there are enough left at port
		if ($port->getGoodAmount($good_id) < $amount) {
			create_error('I\'m short of ' . $good_name . '. So I\'m not going to sell you ' . $amount . ' pcs.');
		}

		$transaction = $port->getGoodTransaction($good_id);

		// does we have what we are going to sell?
		if ($transaction === TransactionType::Sell && $amount > $ship->getCargo($good_id)) {
			create_error('Scanning your ship indicates you don\'t have ' . $amount . ' pcs. of ' . $good_name . '!');
		}

		// check if we have enough room for the thing we are going to buy
		if ($transaction === TransactionType::Buy && $amount > $ship->getEmptyHolds()) {
			create_error('Scanning your ship indicates you don\'t have enough free cargo bays!');
		}

		// get relations for us (global + personal)
		$relations = $player->getRelation($port->getRaceID());

		$ideal_price = $this->idealPrice ?? $port->getIdealPrice($good_id, $transaction, $amount, $relations);
		$offered_price = $this->offeredPrice ?? $port->getOfferPrice($ideal_price, $relations, $transaction);

		// nothing should happen here but just to avoid / by 0
		if ($ideal_price === 0 || $offered_price === 0) {
			create_error('Port calculation error...buy more goods.');
		}

		$stealing = Request::get('action', '') === TransactionType::STEAL;

		if (!$stealing && $this->bargainNumber === 0) {
			$container = new ShopGoodsNegotiate(
				goodID: $this->goodID,
				amount: $amount,
				bargainNumber: $this->bargainNumber,
				bargainPrice: $offered_price,
				offeredPrice: $offered_price,
				idealPrice: $ideal_price,
			);
			$container->go();
		}

		if ($stealing) {
			$bargain_price = 0;
		} else {
			$bargain_price = $this->bargainPrice ?? Request::getInt('bargain_price');
			if ($bargain_price <= 0) {
				create_error('You must enter an amount > 0!');
			}
		}

		// check if the guy has enough money
		if ($transaction === TransactionType::Buy && $player->getCredits() < $bargain_price) {
			create_error('You don\'t have enough credits!');
		}

		if ($stealing) {
			if (!$ship->isUnderground()) {
				throw new Exception('Player tried to steal in a non-underground ship!');
			}
			if ($transaction !== TransactionType::Buy) {
				throw new Exception('Player tried to steal a good the port does not sell!');
			}

			// Small chance to get caught stealing
			$catchChancePercent = $port->getMaxLevel() - $port->getLevel() + 1;
			if (rand(1, 100) <= $catchChancePercent) {
				$fine = $ideal_price * ($port->getLevel() + 1);
				// Don't take the trader all the way to 0 credits
				$newCredits = max(5000, $player->getCredits() - $fine);
				$player->setCredits($newCredits);
				$player->decreaseAlignment(5);
				$player->decreaseRelationsByTrade($amount, $port->getRaceID());

				$fineMessage = '<span class="red">A Federation patrol caught you loading stolen goods onto your ship!<br />The stolen goods have been confiscated and you have been fined ' . number_format($fine) . ' credits.</span>';
				$container = new ShopGoods($fineMessage);
				$container->go();
			}
		}

		// can we accept the current price?
		if ($stealing ||
			  ($transaction === TransactionType::Buy && $bargain_price >= $ideal_price) ||
			  ($transaction === TransactionType::Sell && $bargain_price <= $ideal_price)) {

			// base xp is the amount you would get for a perfect trade.
			// this is the absolut max. the real xp can only be smaller.
			$base_xp = Port::getBaseExperience($amount, $port->getGoodDistance($good_id));

			// if offered equals ideal we get a problem (division by zero)
			if ($stealing) {
				$expPercent = 1; // stealing gives full exp
			} else {
				$expPercent = $port->calculateExperiencePercent($ideal_price, $bargain_price, $transaction);
			}
			$gained_exp = IRound($expPercent * $base_xp);

			if ($stealing) {
				$msg_transaction = 'stolen';
				$ship->increaseCargo($good_id, $amount);
				$player->increaseHOF($amount, ['Trade', 'Goods', 'Stolen'], HOF_ALLIANCE);
				$player->increaseHOF($gained_exp, ['Trade', 'Experience', 'Stealing'], HOF_PUBLIC);
				$port->stealGoods($portGood, $amount);
			} elseif ($transaction === TransactionType::Buy) {
				$msg_transaction = 'bought';
				$ship->increaseCargo($good_id, $amount);
				$player->decreaseCredits($bargain_price);
				$player->increaseHOF($amount, ['Trade', 'Goods', 'Bought'], HOF_ALLIANCE);
				$player->increaseHOF($gained_exp, ['Trade', 'Experience', 'Buying'], HOF_PUBLIC);
				$player->decreaseHOF($bargain_price, ['Trade', 'Money', 'Profit'], HOF_PUBLIC);
				$player->increaseHOF($bargain_price, ['Trade', 'Money', 'Buying'], HOF_PUBLIC);
				$port->buyGoods($portGood, $amount, $ideal_price, $bargain_price, $gained_exp);
				$player->increaseRelationsByTrade($amount, $port->getRaceID());
			} else { // $transaction === TransactionType::Sell
				$msg_transaction = 'sold';
				$ship->decreaseCargo($good_id, $amount);
				$player->increaseCredits($bargain_price);
				$player->increaseHOF($amount, ['Trade', 'Goods', 'Sold'], HOF_ALLIANCE);
				$player->increaseHOF($gained_exp, ['Trade', 'Experience', 'Selling'], HOF_PUBLIC);
				$player->increaseHOF($bargain_price, ['Trade', 'Money', 'Profit'], HOF_PUBLIC);
				$player->increaseHOF($bargain_price, ['Trade', 'Money', 'Selling'], HOF_PUBLIC);
				$port->sellGoods($portGood, $amount, $gained_exp);
				$player->increaseRelationsByTrade($amount, $port->getRaceID());
			}

			$player->increaseHOF($gained_exp, ['Trade', 'Experience', 'Total'], HOF_PUBLIC);
			$player->increaseHOF(1, ['Trade', 'Results', 'Success'], HOF_PUBLIC);

			// log action
			$logAction = $stealing ? TransactionType::STEAL : $transaction->value;
			$player->log(LOG_TYPE_TRADING, $logAction . 's ' . $amount . ' ' . $good_name . ' for ' . $bargain_price . ' credits and ' . $gained_exp . ' experience');

			$player->increaseExperience($gained_exp);

			//will use these variables in current sector and port after successful trade
			$tradeMessage = 'You have just ' . $msg_transaction . ' <span class="yellow">' . $amount . '</span> ' . pluralise($amount, 'unit', false) . ' of <span class="yellow">' . $good_name . '</span>';
			if ($bargain_price > 0) {
				$tradeMessage .= ' for <span class="creds">' . $bargain_price . '</span> ' . pluralise($bargain_price, 'credit', false) . '.';
			}

			if ($gained_exp > 0) {
				if ($stealing) {
					$qualifier = 'cunning';
				} elseif ($gained_exp < $base_xp * 0.25) {
					$qualifier = 'novice';
				} elseif ($gained_exp < $base_xp * 0.5) {
					$qualifier = 'mediocre';
				} elseif ($gained_exp < $base_xp * 0.75) {
					$qualifier = 'respectable';
				} elseif ($gained_exp < IRound($base_xp)) {
					$qualifier = 'excellent';
				} else {
					$qualifier = 'peerless';
				}
				$skill = $stealing ? 'thievery' : 'trading';
				$tradeMessage .= '<br />Your ' . $qualifier . ' ' . $skill . ' skills have earned you <span class="exp">' . $gained_exp . ' </span> ' . pluralise($gained_exp, 'experience point', false) . '!';
			}

			if ($ship->getEmptyHolds() === 0) {
				$container = new CurrentSector(tradeMessage: $tradeMessage);
			} else {
				$container = new ShopGoods($tradeMessage);
			}
		} else {
			// lose relations for bad bargain
			$player->decreaseRelationsByTrade($amount, $port->getRaceID());
			$player->increaseHOF(1, ['Trade', 'Results', 'Fail'], HOF_PUBLIC);

			// do we have enough of it?
			$maxTries = 5;
			if ($this->bargainNumber > 1 && rand($this->bargainNumber, $maxTries) >= $maxTries) {
				$player->decreaseRelationsByTrade($amount, $port->getRaceID());
				$player->increaseHOF(1, ['Trade', 'Results', 'Epic Fail'], HOF_PUBLIC);
				create_error('You don\'t want to accept my offer? I\'m sick of you! Get out of here!');
			}

			$port_off = IRound($offered_price * 100 / $ideal_price);
			$trader_off = IRound($bargain_price * 100 / $ideal_price);

			// get relative numbers!
			// be careful! one of this value is negative!
			$port_off_rel = 100 - $port_off;
			$trader_off_rel = 100 - $trader_off;

			// Should we change the offer price?
			// only do something, if we are more off than the trader
			if (abs($port_off_rel) > abs($trader_off_rel)) {
				// get a random number between
				// (port_off) and (100 +/- $trader_off_rel)
				if (100 + $trader_off_rel < $port_off) {
					$offer_modifier = rand(100 + $trader_off_rel, $port_off);
				} else {
					$offer_modifier = rand($port_off, 100 + $trader_off_rel);
				}
				$offered_price = IRound($ideal_price * $offer_modifier / 100);
			}

			// transfer values to next page
			$container = new ShopGoodsNegotiate(
				goodID: $this->goodID,
				amount: $amount,
				bargainNumber: $this->bargainNumber,
				bargainPrice: $bargain_price,
				offeredPrice: $offered_price,
				idealPrice: $ideal_price,
			);
		}

		// only take turns if they bargained
		if (!$stealing) {
			$player->takeTurns(TURNS_PER_TRADE, TURNS_PER_TRADE);
		}

		// go to next page
		$container->go();
	}

}
