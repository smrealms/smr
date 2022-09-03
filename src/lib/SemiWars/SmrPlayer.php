<?php declare(strict_types=1);

use Smr\DatabaseRecord;

class SmrPlayer extends AbstractSmrPlayer {

	protected function __construct(
		protected readonly int $gameID,
		protected readonly int $accountID,
		DatabaseRecord $dbRecord = null
	) {
		parent::__construct($gameID, $accountID, $dbRecord);
		$this->newbieStatus = true;
	}

}
