<?php declare(strict_types=1);
try {
	echo '<pre>';
	// global config
	require_once(realpath(dirname(__FILE__)) . '/../../bootstrap.php');
	// bot config
	require_once(CONFIG . 'npc/config.specific.php');

	debug('Script started');

	// Enable NPC-specific conditions
	Smr\Container\DiContainer::getContainer()->set('NPC_SCRIPT', true);

	$descriptorSpec = [
		0 => ['pipe', 'r'], // stdin is a pipe that the child will read from
		1 => ['pipe', 'w'], // stdout is a pipe that the child will write to
	];
	$engine = proc_open(UCI_CHESS_ENGINE, $descriptorSpec, $pipes);
	$toEngine =& $pipes[0];
	$fromEngine =& $pipes[1];

	function readFromEngine(bool $block = true): void {
		global $fromEngine;
		stream_set_blocking($fromEngine, $block);
		while (($s = fgets($fromEngine)) !== false) {
			debug('<-- ' . trim($s));
			stream_set_blocking($fromEngine, 0);
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
		Smr\Epoch::update();

		foreach (Smr\Chess\ChessGame::getNPCMoveGames(true) as $chessGame) {
			debug('Looking at game: ' . $chessGame->getChessGameID());
			writeToEngine('position fen ' . $chessGame->getFENString(), false);
			writeToEngine('go ' . ($chessGame->getCurrentTurnColour() == Smr\Chess\ChessGame::PLAYER_WHITE ? 'w' : 'b') . 'time ' . UCI_TIME_PER_MOVE_MS, true, false);
			stream_set_blocking($fromEngine, 1);
			while (stripos($move = trim(fgets($fromEngine)), 'bestmove') !== 0) {
				debug('<-- ' . $move);
				if (stripos($move, 'Seg') === 0) {
					// Segfault
					debug('UCI engine segfaulted?');
					exit;
				}
			}
			debug('Move info: ', $move);
			$move = explode(' ', $move);

			debug('Taking move: ', $move[1]);
			debug('Tried move: ' . $chessGame->tryAlgebraicMove($move[1]));
			writeToEngine('ucinewgame', false);
		}
		// Always sleep for a while to make sure that PHP can't run at 100%.
		usleep(UCI_SLEEP_BETWEEN_CYCLES_US);
	}

	fclose($toEngine);
	fclose($fromEngine);
	proc_close($engine);
} catch (Throwable $e) {
	logException($e);
	exit;
}

function debug($message, $debugObject = null) {
	echo date('Y-m-d H:i:s - ') . $message . ($debugObject !== null ? EOL . var_export($debugObject, true) : '') . EOL;
	$db = Smr\Database::getInstance();
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
		$db->write('UPDATE npc_logs SET script_id=' . SCRIPT_ID . ' WHERE log_id=' . SCRIPT_ID);
	}
}
