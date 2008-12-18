#!/usr/bin/php -q
<?

// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include($LIB . 'smr_db.inc');
include(ENGINE . 'smr.inc');

// database objects
$db = new SMR_DB();

for ($i = 10; $i <= 1025; $i = $i + 10)
	$db->query('INSERT into location VALUES(25, $i, 805)');
	
?>

