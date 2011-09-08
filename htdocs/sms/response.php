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

?>