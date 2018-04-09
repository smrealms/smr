<?php

// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

// overwrite database class to use our db
include( LIB . '1.2/SmrMySqlDatabase.class.inc' );

// new db object
$db = new SmrMySqlDatabase();

$db->query('DROP TABLE IF EXISTS player_has_stats_cache');
$db->query('CREATE TABLE player_has_stats_cache (PRIMARY KEY (game_id,account_id)) SELECT * FROM player_has_stats');

$db->query('DROP TABLE IF EXISTS account_has_stats_cache');
$db->query('CREATE TABLE account_has_stats_cache (PRIMARY KEY (account_id)) SELECT * FROM account_has_stats');
