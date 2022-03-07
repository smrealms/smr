<?php declare(strict_types=1);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once(__DIR__ . '/../../bootstrap.php');
require_once(TOOLS . 'discord/GameLink.class.php');
require_once(TOOLS . 'discord/mysql_cleanup.php');
require_once(CONFIG . 'discord/config.specific.php');

error_reporting(E_ALL);

function getCommandPrefix(): string {
	return defined('COMMAND_PREFIX') ? COMMAND_PREFIX : '.';
}

$loggerLevel = defined('LOGGER_LEVEL') ? LOGGER_LEVEL : 'INFO';
$logger = new Logger('discord');
$logger->pushHandler(new StreamHandler('php://stdout', $loggerLevel));

$discord = new Discord\DiscordCommandClient([
	'token' => DISCORD_TOKEN,
	'prefix' => getCommandPrefix(),
	'description' => 'Your automated co-pilot in the Space Merchant Realms universe. Made with DiscordPHP ' . Discord\Discord::VERSION . '.',
	'caseInsensitiveCommands' => true,
	'discordOptions' => [
		'logger' => $logger,
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
$db = Smr\Database::getInstance();
$db->close();

$discord->run();
