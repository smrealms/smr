<?php declare(strict_types=1);

use Smr\Chess\ChessGame;
use Smr\Chess\Colour;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\Epoch;
use Smr\Npc\UciEngine;

function debug(string $message, mixed $debugObject = null): void {
	echo date('Y-m-d H:i:s - ') . $message . ($debugObject !== null ? EOL . var_export($debugObject, true) : '') . EOL;
	$db = Database::getInstance();
	$logID = $db->insertAutoIncrement('npc_logs', [
		'script_id' => defined('SCRIPT_ID') ? SCRIPT_ID : 0,
		'npc_id' => 0,
		'time' => date('Y-m-d H:i:s'),
		'message' => $message,
		'debug_info' => var_export($debugObject, true),
		'var' => '',
	]);

	// On the first call to debug, we need to update the script_id retroactively
	if (!defined('SCRIPT_ID')) {
		define('SCRIPT_ID', $logID);
		$db->update(
			'npc_logs',
			['script_id' => SCRIPT_ID],
			['log_id' => SCRIPT_ID],
		);
	}
}

try {
	// global config
	require_once(realpath(__DIR__) . '/../../bootstrap.php');

	debug('Script started');

	// Enable NPC-specific conditions
	DiContainer::getContainer()->set('NPC_SCRIPT', true);

	$engine = UciEngine::start(UCI_CHESS_ENGINE, debug: true);

	$engine->send('uci');
	$engine->send('setoption name Hash value ' . UCI_HASH_SIZE_MB, block: false);
	$engine->send('isready');
	$engine->send('ucinewgame', block: false);

	while (true) {
		// The next "page request" must occur at an updated time.
		Epoch::update();

		foreach (ChessGame::getNPCMoveGames(true) as $chessGame) {
			debug('Looking at game: ' . $chessGame->getChessGameID());
			$engine->send('position fen ' . $chessGame->getBoard()->getFEN(), block: false);
			$colour = $chessGame->getCurrentTurnColour() === Colour::White ? 'w' : 'b';
			$engine->send('go ' . $colour . 'time ' . UCI_TIME_PER_MOVE_MS, read: false);
			$move = '';
			while (!str_starts_with($move, 'bestmove')) {
				$move = $engine->readResponse();
				if ($move === null) {
					throw new Exception('Failed to get move from the UCI engine');
				}
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
			$engine->send('ucinewgame', block: false);
		}
		// Always sleep for a while to make sure that PHP can't run at 100%.
		usleep(UCI_SLEEP_BETWEEN_CYCLES_US);
	}

} catch (Throwable $e) {
	logException($e);
}
