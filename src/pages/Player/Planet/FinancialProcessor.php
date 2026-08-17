<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Exception;
use Smr\Html\Submit;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class FinancialProcessor extends PlayerPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionWithdraw;
	public readonly Submit $actionDeposit;

	public function __construct() {
		$this->actionWithdraw = new Submit(self::ACTION, 'Withdraw');
		$this->actionDeposit = new Submit(self::ACTION, 'Deposit');
	}

	public function build(Player $player): never {
		if (!$player->isLandedOnPlanet()) {
			create_error('You are not on a planet!');
		}
		$planet = $player->getSectorPlanet();
		$action = Request::get(self::ACTION);

		// Player has requested a planetary fund transaction
		$amount = Request::getInt('amount');
		if ($amount <= 0) {
			create_error('You must actually enter an amount > 0!');
		}

		if ($action === $this->actionDeposit->value) {
			if ($player->getCredits() < $amount) {
				create_error('You don\'t own that much money!');
			}

			$amount = $planet->increaseCredits($amount); // handles overflow
			$player->decreaseCredits($amount);
		} elseif ($action === $this->actionWithdraw->value) {
			if ($planet->getCredits() < $amount) {
				create_error('There are not enough credits in the planetary account!');
			}

			$amount = $player->increaseCredits($amount); // handles overflow
			$planet->decreaseCredits($amount);
		} else {
			throw new Exception('Unhandled action: ' . $action);
		}
		$player->log(LOG_TYPE_BANK, $action . ' ' . $amount . ' credits at planet');

		new Financial()->go();
	}

}
