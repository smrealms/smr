<?

// ********************************
// *
// * I n c l u d e s   h e r e
// *
// ********************************

include('config.inc');
require_once(LIB . 'global/smr_db.inc');

// new db object
$db = new SMR_DB();

$db->query('SELECT * FROM active_session
			WHERE last_accessed >= ' . (time() - 600));
$count_real_last_active = $db->nf();

$db->query('SELECT * FROM player ' .
		   'WHERE last_cpl_action >= ' . (time() - 600));
$count_last_active = $db->nf();

// fix it if some1 is using the logoff button
if ($count_real_last_active < $count_last_active)
	$count_real_last_active = $count_last_active;

echo('total:'.$count_real_last_active.' active:'.$count_last_active);

?>