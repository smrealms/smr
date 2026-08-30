<?php declare(strict_types=1);

namespace SmrTest\Fakes;

use Override;
use Smr\DatabaseRecord;
use Smr\Player;

class PlayerFake extends Player {

	#[Override]
	public readonly array $SQLID;

	public function __construct(
		protected readonly int $gameID,
		protected readonly int $accountID,
		int $playerID = 1,
		?DatabaseRecord $dbRecord = null,
	) {
		$this->SQLID = [
			'player_id' => $playerID,
		];
		$this->playerID = $playerID;
		assert($dbRecord === null); // avoid PHPStan unused argument warning
	}

}
