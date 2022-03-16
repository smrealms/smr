<?php declare(strict_types=1);

class SmrAccount extends AbstractSmrAccount {

	protected function __construct(int $accountID) {
		parent::__construct($accountID);
		$this->veteranForced = true;
	}

}
