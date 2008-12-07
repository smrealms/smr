<?

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
$active_accounts = $db->nf();

$PHP_OUTPUT.=('active:' . $active_accounts);

?>
