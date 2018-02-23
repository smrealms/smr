<?php
try {
	echo '<pre>';
	// global config
	require_once(realpath(dirname(__FILE__)) . '/../../htdocs/config.inc');
	// bot config
	require_once(CONFIG . 'npc/config.specific.php');
	// needed libs
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(LIB . 'Default/Globals.class.inc');

	$db = new SmrMySqlDatabase();

	debug('Script started');
	define('SCRIPT_ID', $db->getInsertID());
	$db->query('UPDATE npc_logs SET script_id='.SCRIPT_ID.' WHERE log_id='.SCRIPT_ID);

	define('NPC_SCRIPT', true);

	$descriptorSpec = array(
		0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
		1 => array("pipe", "w")  // stdout is a pipe that the child will write to
	);
	$engine = proc_open(UCI_CHESS_ENGINE, $descriptorSpec, $pipes);
	$toEngine =& $pipes[0];
	$fromEngine =& $pipes[1];

	function readFromEngine($block = true) {
		global $fromEngine;
		stream_set_blocking($fromEngine, $block == true ? 1 : 0);
		while(($s = fgets($fromEngine)) !== false) {
			debug('<-- ' . trim($s));
			stream_set_blocking($fromEngine, 0);
		}
	}
	function writeToEngine($s, $block = true, $read = true) {
		global $toEngine;
		debug('--> ' . $s);
		fputs($toEngine, $s . EOL);
		if($read === true) {
			readFromEngine($block);
		}
	}

	readFromEngine();
	writeToEngine('uci');
	writeToEngine('setoption name Hash value ' . UCI_HASH_SIZE_MB, false);
	writeToEngine('isready');
	writeToEngine('ucinewgame', false);
	SmrSession::$game_id = NPC_GAME_ID;

	require_once(get_file_loc('ChessGame.class.inc'));
	while(true) {
		//Redefine MICRO_TIME and TIME, the rest of the game expects them to be the single point in time that the script is executing, with it being redefined for each page load - unfortunately NPCs are one consistent script so we have to do a hack and redefine it (or change every instance of the TIME constant).
		runkit_constant_redefine('MICRO_TIME', microtime());
		runkit_constant_redefine('TIME', (int)microtimeSec(MICRO_TIME));
		
		$chessGames =& ChessGame::getNPCMoveGames(true);
		foreach($chessGames as &$chessGame) {
			debug('Looking at game: ' . $chessGame->getChessGameID());
			writeToEngine('position fen ' . $chessGame->getFENString(), false);
			writeToEngine('go ' . ($chessGame->getCurrentTurnColour() == ChessGame::PLAYER_WHITE ? 'w' : 'b') . 'time ' . UCI_TIME_PER_MOVE_MS, true, false);
			stream_set_blocking($fromEngine, 1);
			while(stripos($move = trim(fgets($fromEngine)), 'bestmove') !== 0) {
				debug('<-- ' . $move);
				if(stripos($move, 'Seg') === 0) {
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
}
catch(Exception $e) {
	logException($e);
	exit;
}
function debug($message, $debugObject = null) {
	global $account,$var,$db;
	echo date('Y-m-d H:i:s - ').$message.($debugObject!==null?EOL.var_export($debugObject,true):'').EOL;
	$db->query('INSERT INTO npc_logs (script_id, npc_id, time, message, debug_info, var) VALUES ('.(defined('SCRIPT_ID')?SCRIPT_ID:0).', '.(is_object($account)?$account->getAccountID():0).',NOW(),'.$db->escapeString($message).','.$db->escapeString(var_export($debugObject,true)).','.$db->escapeString(var_export($var,true)).')');
}
?>
