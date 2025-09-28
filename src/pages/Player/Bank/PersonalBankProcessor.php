<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\Html\Submit;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class PersonalBankProcessor extends PlayerPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionDeposit;
	public readonly Submit $actionWithdraw;

	public function __construct() {
		$this->actionDeposit = new Submit(self::ACTION, 'Deposit');
		$this->actionWithdraw = new Submit(self::ACTION, 'Withdraw');
	}

	public function build(Player $player): never {
		$amount = Request::getInt('amount');
		$action = Request::get(self::ACTION);

		// no negative amounts are allowed
		if ($amount <= 0) {
			create_error('You must actually enter an amount > 0!');
		}

		if ($action === $this->actionDeposit->value) {
			if ($player->getCredits() < $amount) {
				create_error('You don\'t have that much money on your ship!');
			}
			$amount = $player->increaseBank($amount); // handles overflow
			$player->decreaseCredits($amount);
		} else {
			if ($player->getBank() < $amount) {
				create_error('You don\'t have that much money in your account!');
			}
			$amount = $player->increaseCredits($amount); // handles overflow
			$player->decreaseBank($amount);
		}

		// log action
		$player->log(LOG_TYPE_BANK, $action . ' ' . $amount . ' credits for personal account');

		(new PersonalBank())->go();
	}

}
