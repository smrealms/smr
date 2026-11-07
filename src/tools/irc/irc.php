<?php declare(strict_types=1);

use Smr\Irc\IrcDriver;

function echo_r(string $message): void {
	echo date('Y-m-d H:i:s => ') . $message . EOL;
}

// config file
require_once(realpath(__DIR__) . '/../../bootstrap.php');

$options = getopt('', ['debug']);
if ($options === false) {
	throw new Exception('Failed to parse options.');
}
$debugging = isset($options['debug']);

require_once(TOOLS . 'irc/stream.php');

$ircDriver = new IrcDriver($debugging);
$ircDriver->run();
