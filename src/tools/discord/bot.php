<?php declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Smr\Database;
use Smr\Discord\Commands\Forces;
use Smr\Discord\Commands\Game;
use Smr\Discord\Commands\Invite;
use Smr\Discord\Commands\MagicEightBall;
use Smr\Discord\Commands\Money;
use Smr\Discord\Commands\Op;
use Smr\Discord\Commands\OpList;
use Smr\Discord\Commands\OpTurns;
use Smr\Discord\Commands\Seed;
use Smr\Discord\Commands\Seedlist;
use Smr\Discord\Commands\SeedlistAdd;
use Smr\Discord\Commands\SeedlistDel;
use Smr\Discord\Commands\Turns;

require_once(__DIR__ . '/../../bootstrap.php');

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
(new Forces())->register($discord);
(new Game())->register($discord);
(new Invite($discord))->register($discord);
(new MagicEightBall())->register($discord);
(new Money())->register($discord);
$opCmd = (new Op())->register($discord);
(new OpList())->register($opCmd);
(new OpTurns())->register($opCmd);
(new Seed())->register($discord);
$seedlistCmd = (new Seedlist())->register($discord);
(new SeedlistAdd())->register($seedlistCmd);
(new SeedlistDel())->register($seedlistCmd);
(new Turns())->register($discord);

// Close the connection we may have opened during startup
// to avoid a mysql timeout.
Database::resetInstance();

$discord->run();
