#!/usr/bin/php -q
<?php

// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include(LIB . 'Default/SmrMySqlDatabase.class.inc');
include(ENGINE . 'smr.inc');

// database objects
$db = new SmrMySqlDatabase();

for ($i = 10; $i <= 1025; $i = $i + 10)
	$db->query('INSERT into location VALUES(25, '.$i.', 805)');
	
?>

