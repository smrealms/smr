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


?>