<?php
require_once(get_file_loc('AbstractSmrAccount.class.inc'));
class SmrAccount extends AbstractSmrAccount {
	function __construct($accountID) {
		parent::__construct($accountID);
		$this->veteran = true;
	}
}

?>