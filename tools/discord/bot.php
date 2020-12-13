<?php declare(strict_types=1);

require_once(__DIR__ . '/../../htdocs/config.inc');
require_once(LIB . 'Default/smr.inc');
require_once(TOOLS . 'discord/GameLink.inc');
require_once(TOOLS . 'discord/mysql_cleanup.php');
require_once(CONFIG . 'discord/config.specific.php');

error_reporting(E_ALL);

function getCommandPrefix() : string {
	return defined('COMMAND_PREFIX') ? COMMAND_PREFIX : '.';
}

$discord = new Discord\DiscordCommandClient([
	'token' => DISCORD_TOKEN,
	'prefix' => getCommandPrefix(),
	'description' => 'Your automated co-pilot in the Space Merchant Realms universe. Made with DiscordPHP ' . Discord\Discord::VERSION . '.',
	'discordOptions' => [
		'loggerLevel' => defined('LOGGER_LEVEL') ? LOGGER_LEVEL : 'INFO',
	],
]);

// Set bot presence to "Listening to <help command>"
$discord->on('ready', function($discord) {
	$activity = $discord->factory(Discord\Parts\User\Activity::class, [
		'name' => getCommandPrefix() . 'help',
		'type' => Discord\Parts\User\Activity::TYPE_LISTENING,
	]);
	$discord->updatePresence($activity);
});

// Register commands
require_once('commands/money.php');
require_once('commands/game.php');
require_once('commands/turns.php');
require_once('commands/invite.php');
require_once('commands/op.php');
require_once('commands/seed.php');
require_once('commands/seedlist.php');
require_once('commands/forces.php');
require_once('commands/8ball.php');

// Close the connection we may have opened during startup
// to avoid a mysql timeout.
$db = MySqlDatabase::getInstance();
$db->close();

$discord->run();
