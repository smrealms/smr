<?php

class SmrAccount extends AbstractSmrAccount {
	function __construct($accountID) {
		parent::__construct($accountID);
		$this->veteran = true;
	}
}
