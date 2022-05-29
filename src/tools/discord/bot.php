<?php declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once(__DIR__ . '/../../bootstrap.php');
require_once(TOOLS . 'discord/GameLink.php');
require_once(TOOLS . 'discord/mysql_cleanup.php');

error_reporting(E_ALL);

$logger = new Logger('discord');
$logger->pushHandler(new StreamHandler('php://stdout', DISCORD_LOGGER_LEVEL));

$discord = new Discord\DiscordCommandClient([
	'token' => DISCORD_TOKEN,
	'prefix' => DISCORD_COMMAND_PREFIX,
	'description' => 'Your automated co-pilot in the Space Merchant Realms universe. Made with DiscordPHP ' . Discord\Discord::VERSION . '.',
	'caseInsensitiveCommands' => true,
	'discordOptions' => [
		'logger' => $logger,
	],
]);

// Set bot presence to "Listening to <help command>"
$discord->on('ready', function($discord) {
	$activity = $discord->factory(Discord\Parts\User\Activity::class, [
		'name' => DISCORD_COMMAND_PREFIX . 'help',
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
