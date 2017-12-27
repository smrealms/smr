<?php

require_once(__DIR__.'/../../htdocs/config.inc');
require_once(ENGINE . 'Default/smr.inc');
require_once(TOOLS . 'discord/GameLink.inc');
require_once(TOOLS . 'discord/mysql_cleanup.php');
require_once(CONFIG . 'discord/config.specific.php');

error_reporting(E_ALL);

$discord = new Discord\DiscordCommandClient([
	'token' => DISCORD_TOKEN,
	'prefix' => defined('COMMAND_PREFIX') ? COMMAND_PREFIX : '.',
	'discordOptions' => (['loggerLevel' => 'INFO']),
]);

// Register commands
require_once('commands/money.php');
require_once('commands/game.php');
require_once('commands/turns.php');
require_once('commands/invite.php');
require_once('commands/op.php');
require_once('commands/seed.php');
require_once('commands/seedlist.php');
require_once('commands/forces.php');

// Close the connection we may have opened during startup
// to avoid a mysql timeout.
$db = new SmrMySqlDatabase();
$db->close();

$discord->run();
