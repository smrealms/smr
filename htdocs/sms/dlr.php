<?php

/*
    http://www.smrealms.de/sms/dlr.php?message_id=%message_id%&send=%send%&receive=%receive%&status=%status%&ref=%ref%&to=%to%

	%message_id%    Message ID of outgoing text from gateway
	%send%          sending time as timestamp
	%receive%       receiving time as timestamp
	%status%        sending result
	%ref%           reference (can be given when sending)
	%to%            where text was being send to
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