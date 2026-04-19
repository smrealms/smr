<?php declare(strict_types=1);

namespace SmrTest\Fakes;

use Smr\DatabaseRecord;
use Smr\Player;

class PlayerFake extends Player {

	public readonly array $SQLID;

	public function __construct(
		protected readonly int $gameID,
		protected readonly int $accountID,
		?DatabaseRecord $dbRecord = null,
	) {
		$this->SQLID = [
			'account_id' => $accountID,
			'game_id' => $gameID,
		];
		assert($dbRecord === null); // avoid PHPStan unused argument warning
	}

}
