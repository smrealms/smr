<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;

class CheatingShipCheckProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $hardwareTypeID,
		private readonly int $maxAmount,
		private readonly int $accountID
	) {}

	public function build(Account $account): never {
		//get our variables
		$game_id = $this->gameID;
		$hardware_id = $this->hardwareTypeID;
		$max_amount = $this->maxAmount;
		$account_id = $this->accountID;

		//update it so they arent cheating
		$db = Database::getInstance();
		$db->write('UPDATE ship_has_hardware
					SET amount = :amount
					WHERE game_id = :game_id AND
						account_id = :account_id AND
						hardware_type_id = :hardware_type_id', [
			'amount' => $db->escapeNumber($max_amount),
			'game_id' => $db->escapeNumber($game_id),
			'account_id' => $db->escapeNumber($account_id),
			'hardware_type_id' => $db->escapeNumber($hardware_id),
		]);

		//now erdirect back to page
		$container = new CheatingShipCheck();
		$container->go();
	}

}
