<?php

// ********************************
// *
// * I n c l u d e s   h e r e
// *
// ********************************

include('config.inc');
require_once($LIB . 'global/smr_db.inc');

// new db object
$db = new SMR_DB();

$db->query('SELECT * FROM account WHERE last_login > ' . (time() - 604800));
$result = $db->nf();
$PHP_OUTPUT.=($result);
$PHP_OUTPUT.=('...');

$db->query('SELECT * FROM active_session WHERE last_accessed > ' . (time() - 600));
$result = $db->nf();
$PHP_OUTPUT.=($result);
$PHP_OUTPUT.=('...');

$db->query('SELECT * FROM player WHERE last_cpl_action > ' . (time() - 600) . ' GROUP BY game_id');
$result = $db->nf();
echo($result);

?>
