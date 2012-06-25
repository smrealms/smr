<?php

/*

	http://www.smrealms.de/sms/response.php?message_id=%message_id%&message=%message%&from=%from%&ref=%ref%

	%message_id%    Message ID of text that is being responded to
	%message%       text of response
	%from%          cell number of responder
	%ref%           reference

 */

include('../config.inc');

include(LIB . 'Default/SmrMySqlDatabase.class.inc');
include(ENGINE . '/Default/smr.inc');

// database object
$db = new SmrMySqlDatabase();

// get input
$message_id = (int)$_GET['message_id'];
$message = $_GET['message'];
$from = $_GET['from'];

// add dlr to database
$db->query(
	'INSERT INTO account_sms_response ' .
	'(message_id, message, from) ' .
	'VALUES (' . $db->escapeNumber($message_id) . ', ' . $db->escapeString($message) . ', ' . $db->escapeString($from) . ')');

?>