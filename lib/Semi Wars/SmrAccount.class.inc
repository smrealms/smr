<?php

class SmrAccount extends AbstractSmrAccount {
	protected function __construct($accountID) {
		parent::__construct($accountID);
		$this->veteranForced = true;
	}
}
