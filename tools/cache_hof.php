<?

// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

// overwrite database class to use our db
include( LIB . 'global/smr_db.inc' );

// new db object
$db = new SMR_DB();

$db->query('DROP TABLE IF EXISTS player_has_stats_cache');
$db->query('CREATE TABLE player_has_stats_cache (PRIMARY KEY (game_id,account_id)) SELECT * FROM player_has_stats');

$db->query('DROP TABLE IF EXISTS account_has_stats_cache');
$db->query('CREATE TABLE account_has_stats_cache (PRIMARY KEY (account_id)) SELECT * FROM account_has_stats');

?>
