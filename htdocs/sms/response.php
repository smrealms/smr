<?php

/*

%message_id% Message ID der ursprünglich versendeten Nachricht
%message% Inhalt der SMS Antwort
%from% Absender der Antwort
%ref% Referenz der versendeten Nachricht

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