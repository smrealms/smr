<?php declare(strict_types=1);

use Smr\Chess\ChessGame;
use Smr\Chess\Colour;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\Epoch;

function debug(string $message, mixed $debugObject = null): void {
	echo date('Y-m-d H:i:s - ') . $message . ($debugObject !== null ? EOL . var_export($debugObject, true) : '') . EOL;
	$db = Database::getInstance();
	$logID = $db->insert('npc_logs', [
		'script_id' => defined('SCRIPT_ID') ? SCRIPT_ID : 0,
		'npc_id' => 0,
		'time' => 'NOW()',
		'message' => $db->escapeString($message),
		'debug_info' => $db->escapeString(var_export($debugObject, true)),
		'var' => $db->escapeString(''),
	]);

	// On the first call to debug, we need to update the script_id retroactively
	if (!defined('SCRIPT_ID')) {
		define('SCRIPT_ID', $logID);
		$db->write('UPDATE npc_logs SET script_id = :script_id WHERE log_id = :script_id', [
			'script_id' => SCRIPT_ID,
		]);
	}
}

try {
	// global config
	require_once(realpath(__DIR__) . '/../../bootstrap.php');

	debug('Script started');

	// Enable NPC-specific conditions
	DiContainer::getContainer()->set('NPC_SCRIPT', true);

	$descriptorSpec = [
		0 => ['pipe', 'r'], // stdin is a pipe that the child will read from
		1 => ['pipe', 'w'], // stdout is a pipe that the child will write to
	];
	proc_open(UCI_CHESS_ENGINE, $descriptorSpec, $pipes);
	$toEngine =& $pipes[0];
	$fromEngine =& $pipes[1];

	function readFromEngine(bool $block = true): void {
		global $fromEngine;
		stream_set_blocking($fromEngine, $block);
		while (($s = fgets($fromEngine)) !== false) {
			debug('<-- ' . trim($s));
			stream_set_blocking($fromEngine, false);
		}
	}
	function writeToEngine(string $s, bool $block = true, bool $read = true): void {
		global $toEngine;
		debug('--> ' . $s);
		fwrite($toEngine, $s . EOL);
		if ($read === true) {
			readFromEngine($block);
		}
	}

	readFromEngine();
	writeToEngine('uci');
	writeToEngine('setoption name Hash value ' . UCI_HASH_SIZE_MB, false);
	writeToEngine('isready');
	writeToEngine('ucinewgame', false);

	while (true) {
		// The next "page request" must occur at an updated time.
		Epoch::update();

		foreach (ChessGame::getNPCMoveGames(true) as $chessGame) {
			debug('Looking at game: ' . $chessGame->getChessGameID());
			writeToEngine('position fen ' . $chessGame->getBoard()->getFEN(), false);
			writeToEngine('go ' . ($chessGame->getCurrentTurnColour() == Colour::White ? 'w' : 'b') . 'time ' . UCI_TIME_PER_MOVE_MS, true, false);
			stream_set_blocking($fromEngine, true);
			$move = '';
			while (!str_starts_with($move, 'bestmove')) {
				$move = fgets($fromEngine);
				if ($move === false) {
					throw new Exception('Failed to get move from the UCI engine');
				}
				$move = trim($move);
				debug('<-- ' . $move);
				if (str_starts_with($move, 'Seg')) {
					// Segfault
					debug('UCI engine segfaulted?');
					exit;
				}
			}
			debug('Move info: ', $move);
			$move = explode(' ', $move);

			debug('Taking move: ', $move[1]);
			$chessGame->tryAlgebraicMove($move[1]);
			writeToEngine('ucinewgame', false);
		}
		// Always sleep for a while to make sure that PHP can't run at 100%.
		usleep(UCI_SLEEP_BETWEEN_CYCLES_US);
	}

} catch (Throwable $e) {
	logException($e);
}
