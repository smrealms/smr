<?php

/*
	%message_id%    Message ID der versendeten Nachricht
	%send%          Versandzeitpunkt als Unix Timestamp
	%receive%       Empfangszeitpunkt als Unix Timestamp
	%status%        Versandbericht
	%ref%           Referenz der versendeten Nachricht
	%to%            Empfängernummer der Nachricht
 */

include('../config.inc');

include(LIB . 'Default/SmrMySqlDatabase.class.inc');
include(ENGINE . '/Default/smr.inc');

// database object
$db = new SmrMySqlDatabase();

// get input
$message_id = (int)$_GET['message_id'];
$send_time = (int)$_GET['send'];
$receive_time = (int)$_GET['receive'];
$status = $_GET['status'];

// add dlr to database
$db->query(
	'INSERT INTO account_sms_dlr ' .
	'(message_id, send_time, receive_time, status, announce) ' .
	'VALUES (' . $db->escapeNumber($message_id) . ', ' . $db->escapeNumber($send_time) . ', ' . $db->escapeNumber($receive_time) . ', ' . $db->escapeString($status) . ')');

?>