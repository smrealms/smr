<?php

define('ERROR', 2);
define('WARNING', 1);
define('DEBUG', 0);

$LOG_LEVEL = array('DEBUG  ', 'WARNING', 'ERROR  ');

function log_message($account_id, $message, $level = 0) {

	global $LOG_LEVEL;

	$login = get_account($account_id, 'login');

	$PHP_OUTPUT.=(date('M j  H:i:s') . ' - ' . $login . ' - ' . $LOG_LEVEL[$level] . ' - ' . $message . ''.EOL);

	if ($level == ERROR)
		exit;

}

?>