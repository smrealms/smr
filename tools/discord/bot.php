<?php

require_once(__DIR__.'/../../htdocs/config.inc');
require_once(ENGINE . 'Default/smr.inc');
require_once(TOOLS . 'discord/GameLink.inc');
require_once(CONFIG . 'discord/config.specific.php');

error_reporting(E_ALL);

$discord = new Discord\DiscordCommandClient([
	'token' => DISCORD_TOKEN,
	'prefix' => '.',
	'discordOptions' => (['loggerLevel' => 'INFO']),
]);

// Register commands
require_once('commands/money.php');
require_once('commands/game.php');
require_once('commands/turns.php');
require_once('commands/invite.php');

$discord->run();
