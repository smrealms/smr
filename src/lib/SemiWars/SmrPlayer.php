<?php declare(strict_types=1);

class SmrPlayer extends AbstractSmrPlayer {

	protected function __construct(
		protected readonly int $gameID,
		protected readonly int $accountID,
		Smr\DatabaseRecord $dbRecord = null
	) {
		parent::__construct($gameID, $accountID, $dbRecord);
		$this->newbieStatus = true;
	}

}
