<?php
require_once(get_file_loc('SmrPlayer.class.inc'));
require_once(get_file_loc('ChessPiece.class.inc'));
/**
 * @author Page
 *
 */
class ChessGame {
	const GAMETYPE_STANDARD = 'Standard';
	const PLAYER_BLACK = 'Black';
	const PLAYER_WHITE = 'White';

	private $db;
	
	private $chessGameID;
	private $startDate;
	private $endDate;
	private $gameType;
	private $winner;

	private $hasMoved;
	private $board;
	private $moves;
	private $messages;
	
	public function __construct($chessGameID) {
		$this->db = new SmrMySqlDatabase();
		$this->db->query('SELECT *' .
				' FROM chess_game' .
				' WHERE chess_game_id=' . $chessGameID . ' LIMIT 1;');
		if($this->db->nextRecord()) {
			$this->chessGameID = $chessGameID;
			$this->startDate = $this->db->getInt('start_time');
			$this->endDate = $this->db->getInt('end_time');
			$this->whiteID = $this->db->getInt('white_id');
			$this->blackID = $this->db->getInt('black_id');
			$this->winner = $this->db->getInt('winner_id');
			$this->resetHasMoved();
		}
		else {
			throw new Exception('Chess game not found: ' . $chessGameID);
		}
	}
	
	public static function isValidCoord($x, $y, array &$board) {
		return $y < count($board) && $y >= 0 && $x < count($board[$y]) && $x >= 0;
	}
	
	private function resetHasMoved() {
		$this->hasMoved = array(
			self::PLAYER_WHITE => array(
				ChessPiece::KING => false,
				ChessPiece::ROOK => array(
					'Queen' => false,
					'King' => false
				)
			),
			self::PLAYER_BLACK => array(
				ChessPiece::KING => false,
				ChessPiece::ROOK => array(
					'Queen' => false,
					'King' => false
				)
			),
			ChessPiece::PAWN => array(-1,-1)
		);
	}
	
	public function rerunGame($debugInfo = false) {
		$db = new SmrMySqlDatabase();
		$db2 = new SmrMySqlDatabase();
		
		$db->query('UPDATE chess_game
					SET end_time = NULL, winner_id = 0
					WHERE chess_game_id=' . $this->chessGameID . ';');
		$db->query('DELETE FROM chess_game_pieces WHERE chess_game_id = ' . $this->chessGameID . ';');
		self::insertPieces($this->chessGameID, $this->getWhitePlayer(), $this->getBlackPlayer());
		
		$db->query('SELECT * FROM chess_game_moves WHERE chess_game_id = ' . $this->chessGameID . ' ORDER BY move_id;');
		$db2->query('DELETE FROM chess_game_moves WHERE chess_game_id = ' . $this->chessGameID . ';');
		$this->moves = array();
		$this->board = null;
		$this->endDate = null;
		$this->winner = null;
		$this->resetHasMoved();

		try {
			while($db->nextRecord()) {
				if($debugInfo === true) {
					echo 'x=', $db->getInt('start_x'), ', y=', $db->getInt('start_y'), ', endX=', $db->getInt('end_x'), ', endY=', $db->getInt('end_y'), ', forAccountID=', $db->getInt('move_id') % 2 == 1 ? $this->getWhiteID() : $this->getBlackID(), EOL;
				}
				if(0 != $this->tryMove($db->getInt('start_x'), $db->getInt('start_y'), $db->getInt('end_x'), $db->getInt('end_y'), $db->getInt('move_id') % 2 == 1 ? $this->getWhiteID() : $this->getBlackID())) {
					break;
				}
			}
		}
		catch(Exception $e) {
			if($debugInfo === true) {
				echo $e->getMessage() . EOL . $e->getTraceAsString() . EOL;
			}
			// We probably tried an invalid move - move on.
		}
	}
	
	public function getBoard() {
		if($this->board == null) {
			$this->db->query('SELECT * FROM chess_game_pieces WHERE chess_game_id=' . $this->chessGameID . ';');
			$pieces = array();
			while($this->db->nextRecord()) {
				$accountID = $this->db->getInt('account_id');
				$pieces[] = new ChessPiece($this->chessGameID, $accountID, $this->getColourForAccountID($accountID), $this->db->getInt('piece_id'), $this->db->getInt('x'), $this->db->getInt('y'), $this->db->getInt('piece_no'));
			}
			$this->board =& $this->parsePieces($pieces);
		}
		return $this->board;
	}
	
	public function getMoves() {
		if($this->moves == null) {
			$this->db->query('SELECT * FROM chess_game_moves WHERE chess_game_id = ' . $this->chessGameID . ' ORDER BY move_id;');
			$this->moves = array();
			while($this->db->nextRecord()) {
				$pieceTakenID = $this->db->getField('piece_taken') == null ? null : $this->db->getInt('piece_taken');
				$this->moves[] = $this->createMove($this->db->getInt('piece_id'), $this->db->getInt('start_x'), $this->db->getInt('start_y'), $this->db->getInt('end_x'), $this->db->getInt('end_y'), $pieceTakenID, $this->db->getField('checked'), $this->db->getInt('move_id') % 2 == 1 ? self::PLAYER_WHITE : self::PLAYER_BLACK, $this->db->getField('castling'), $this->db->getBoolean('en_passant'));
			}
		}
		return $this->moves;
	}
	
	public function getMessages() {
		if($this->messages == null) {
			$df = new SimpleDateFormat('yyyy-MM-dd HH:mm:ss');
			$this->db->query('SELECT * FROM chess_game_messages WHERE chess_game_id = ' . $this->chessGameID . ' ORDER BY message_id;');
			$this->messages = array();
			while($this->db->nextRecord()) {
				$this->messages[] = $df->format(new Date($this->db->getInt('sent_time'))) . ' ' . $this->db->getField('sender') . ': ' . $this->db->getField('message');
			}
		}
		return $this->messages;
	}
	
	public function addMessage($accountID, $message) {
		$this->getMessages();
		$this->db->query('INSERT INTO chess_game_messages' .
				'(chess_game_id,sender,sent_time,message)' .
				'values' .
				'(' . $gameID . ',' . $accountID . ',' . TIME . ',' . $message . ');');
		$this->messages[] = $message;
	}
	
	public static function &parsePieces(array &$pieces) {
		$board = array();
		$row = array();
		for($i=0;$i<8;$i++) {
			$row[] = null;
		}
		for($i=0;$i<8;$i++) {
			$board[] = $row;
		}
		foreach($pieces as &$piece) {
			if($board[$piece->getY()][$piece->getX()] != null) {
				throw new Exception('Two pieces found in the same tile.');
			}
			$board[$piece->getY()][$piece->getX()] =& $piece;
		}
		return $board;
	}

	public static function getStandardGame($gameID, AbstractSmrPlayer &$whitePlayer, AbstractSmrPlayer &$blackPlayer) {
		$white = $whitePlayer->getAccountID();
		$black = $blackPlayer->getAccountID();
		return array
			(
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::ROOK, 0, 0),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::KNIGHT, 1, 0),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::BISHOP, 2, 0),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::QUEEN, 3, 0),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::KING, 4, 0),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::BISHOP, 5, 0),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::KNIGHT, 6, 0),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::ROOK, 7, 0),

				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 0, 1),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 1, 1),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 2, 1),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 3, 1),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 4, 1),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 5, 1),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 6, 1),
				new ChessPiece($gameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 7, 1),

				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 0, 6),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 1, 6),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 2, 6),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 3, 6),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 4, 6),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 5, 6),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 6, 6),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 7, 6),
				
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::ROOK, 0, 7),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::KNIGHT, 1, 7),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::BISHOP, 2, 7),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::QUEEN, 3, 7),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::KING, 4, 7),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::BISHOP, 5, 7),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::KNIGHT, 6, 7),
				new ChessPiece($gameID, $white, self::PLAYER_WHITE, ChessPiece::ROOK, 7, 7),
			);
	}
	
	public static function insertNewGame($startDate, $endDate, AbstractSmrPlayer &$whitePlayer, AbstractSmrPlayer &$blackPlayer) {
		if($startDate == null) {
			throw new Exception('Start date cannot be null.');
		}
		
		$db = new SmrMySqlDatabase();
		$db->query('INSERT INTO chess_game' .
				'(start_time,end_time,white_id,black_id)' .
				'values' .
				'(' . $db->escapeNumber($startDate) . ',' . ($endDate == null ? 'NULL' : $db->escapeNumber($endDate)) . ',' . $db->escapeNumber($whitePlayer->getAccountID()) . ',' . $db->escapeNumber($blackPlayer->getAccountID()) . ');');
		$chessGameID = $db->getInsertID();

		self::insertPieces($chessGameID, $whitePlayer, $blackPlayer);
		return $chessGameID;
	}
	
	private static function insertPieces($chessGameID, AbstractSmrPlayer &$whitePlayer, AbstractSmrPlayer &$blackPlayer) {
		$db = new SmrMySqlDatabase();
		$pieces = self::getStandardGame($chessGameID, $whitePlayer, $blackPlayer);
		foreach($pieces as $p) {
			$db->query('INSERT INTO chess_game_pieces' .
			'(chess_game_id,account_id,piece_id,x,y)' .
			'values' .
			'(' . $db->escapeNumber($chessGameID) . ',' . $db->escapeNumber($p->accountID) . ',' . $db->escapeNumber($p->pieceID) . ',' . $db->escapeNumber($p->getX()) . ',' . $db->escapeNumber($p->getY()) . ');');
		}
	}

	public static function &getOngoingAccountGames($accountID) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT chess_game_id FROM chess_game WHERE (black_id = ' . $db->escapeNumber($accountID) . ' OR white_id = ' . $db->escapeNumber($accountID) . ') AND (end_time > ' . TIME . ' OR end_time IS NULL);');
		$games = array();
		while($db->nextRecord()) {
			$games[] = new ChessGame($db->getInt('chess_game_id'));
		}
		return $games;
	}

	public static function &getAccountGames($accountID) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT chess_game_id FROM chess_game WHERE black_id = ' . $db->escapeNumber($accountID) . ' OR white_id = ' . $db->escapeNumber($accountID) . ';');
		$games = array();
		while($db->nextRecord()) {
			$games[] = new ChessGame($db->getInt('chess_game_id'));
		}
		return $games;
	}
	
	public function createMove($pieceID, $startX, $startY, $endX, $endY, $pieceTaken, $checking, $playerColour, $castling, $enPassant) {
		if($pieceID == ChessPiece::KING) {
			$this->hasMoved[$playerColour][ChessPiece::KING] = true;
		}
		if($pieceID == ChessPiece::ROOK && ($startX == 0 || $startX == 7) && ($startY == 0 || $startY == 7)) {
			$this->hasMoved[$playerColour][ChessPiece::ROOK][$startX==0?'Queen':'King'] = true;
		}
		if($pieceID == ChessPiece::PAWN && ($startY == 1 || $startY == 6) && ($endY == 3 || $endY == 4)) {
			$this->hasMoved[ChessPiece::PAWN] = array($endX, $endY);
		}
		else {
			$this->hasMoved[ChessPiece::PAWN] = array(-1,-1);
		}
		return ($castling == 'Queen' ? '0-0-0' : ($castling == 'King' ? '0-0' : ''))
			. ChessPiece::getSymbolForPiece($pieceID, $playerColour)
			. chr(ord('a')+$startX)
			. (8-$startY)
			. ' '
			. ($pieceTaken == null ? '' : ChessPiece::getSymbolForPiece($pieceTaken, $playerColour == self::PLAYER_WHITE ? self::PLAYER_BLACK : self::PLAYER_WHITE))
			. chr(ord('a')+$endX)
			. (8-$endY)
			. ' '
			. ($checking == null ? '' : ($checking  == 'CHECK' ? '+' : '++') )
			. ($enPassant ? ' e.p.' : '');
	}
	
	public function isCheckmated($colour) {
		$board =& $this->board;
		$hasMoved =& $this->getHasMoved();
		$king = null;
		foreach($board as $row) {
			foreach($row as $piece) {
				if($piece != null && $piece->pieceID == ChessPiece::KING && $piece->colour == $colour) {
					$king = $piece;
					break;
				}
			}
		}
		if($king == null) {
			throw new Exception('Could not find the king: game id = ' . $this->chessGameID);
		}
		if(!$king->isChecked($board, $hasMoved)) {
			return false;
		}
		foreach($board as $row) {
			foreach($row as $piece) {
				if($piece != null && $piece->colour == $colour) {
					$moves = $piece->getPossibleMoves($board, $hasMoved);
					//There are moves we can make, we are clearly not checkmated.
					if(count($moves) > 0) {
						return false;
					}
				}
			}
		}
		return true;
	}
	
	public static function isCastling($x, $toX) {
		$movement = $toX - $x;
		if(abs($movement) == 2) {
			//To the left.
			if($movement == -2) {
				return array('Type' => 'Queen',
						'X' => 0,
						'ToX' => 3
					);
			} //To the right
			else if($movement == 2) {
				return array('Type' => 'King',
						'X' => 7,
						'ToX' => 5
					);
			}
		}
		return false;
	}
	
	public static function movePiece(array &$board, array &$hasMoved, $x, $y, $toX, $toY) {
		if(!self::isValidCoord($x, $y, $board)) {
			throw new Exception('Invalid from coordinates, x=' . $x . ', y=' . $y);
		}
		if(!self::isValidCoord($toX, $toY, $board)) {
			throw new Exception('Invalid to coordinates, x=' . $toX . ', y=' . $toY);
		}
		$pieceTaken = $board[$toY][$toX];
		$board[$toY][$toX] = $board[$y][$x];
		$p =& $board[$toY][$toX];
		$board[$y][$x] = null;
		if($p == null) {
			throw new Exception('Trying to move non-existent piece: ' . var_export($board, true));
		}
		$p->setX($toX);
		$p->setY($toY);

		$oldPawnMovement = $hasMoved[ChessPiece::PAWN];
		$nextPawnMovement = array(-1,-1);
		$castling = false;
		$enPassant = false;
		$rookMoved = false;
		if($p->pieceID == ChessPiece::KING) {
			//Castling?
			$castling = self::isCastling($x, $toX);
			if($castling !== false) {
				$hasMoved[$p->colour][ChessPiece::KING] = true;
				$hasMoved[$p->colour][ChessPiece::ROOK][$castling['Type']] = true;
				if($board[$y][$castling['X']] == null) {
					throw new Exception('Cannot castle with non-existent castle.');
				}
				$board[$toY][$castling['ToX']] = $board[$y][$castling['X']];
				$board[$toY][$castling['ToX']]->setX($castling['ToX']);
				$board[$y][$castling['X']] = null;
			}
		}
		else if($p->pieceID == ChessPiece::PAWN) {
			//Double move to track?
			if(($y == 1 || $y == 6) && ($toY == 3 || $toY == 4)) {
				$nextPawnMovement = array($toX, $toY);
			}
			//En passant?
			else if($hasMoved[ChessPiece::PAWN][0] == $toX &&
					$hasMoved[ChessPiece::PAWN][1] == 3 && $toY == 2 &&
					$hasMoved[ChessPiece::PAWN][1] == 4 && $toY == 5) {
				$enPassant = true;
				$pieceTaken = $board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]];
				if($board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] == null) {
					throw new Exception('Cannot en passant a non-existent pawn.');
				}
				$board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] = null;
			}
		}
		else if($p->pieceID == ChessPiece::ROOK && ($x == 0 || $x == 7) && ($y == 0 || $y == 7)) {
			//Rook moved?
			if($hasMoved[$p->colour][ChessPiece::ROOK][$x==0?'Queen':'King'] === false) {
				$rookMoved = $x==0?'Queen':'King';
				$hasMoved[$p->colour][ChessPiece::ROOK][$rookMoved] = true;
			}
		}
		$hasMoved[ChessPiece::PAWN] = $nextPawnMovement;
		return array('Castling' => $castling,
				'PieceTaken' => $pieceTaken,
				'EnPassant' => $enPassant,
				'RookMoved' => $rookMoved,
				'OldPawnMovement' => $oldPawnMovement
			);
	}
	
	public static function undoMovePiece(array &$board, array &$hasMoved, $x, $y, $toX, $toY, $moveInfo) {
		if(!self::isValidCoord($x, $y, $board)) {
			throw new Exception('Invalid from coordinates, x=' . $x . ', y=' . $y);
		}
		if(!self::isValidCoord($toX, $toY, $board)) {
			throw new Exception('Invalid to coordinates, x=' . $toX . ', y=' . $toY);
		}
		if($board[$y][$x] != null) {
			throw new Exception('Undoing move onto another piece? x=' . $x . ', y=' . $y);
		}
		$board[$y][$x] = $board[$toY][$toX];
		$p =& $board[$y][$x];
		if($p == null) {
			throw new Exception('Trying to undo move of a non-existent piece: ' . var_export($board, true));
		}
		$board[$toY][$toX] = $moveInfo['PieceTaken'];
		$p->setX($x);
		$p->setY($y);

		$hasMoved[ChessPiece::PAWN] = $moveInfo['OldPawnMovement'];
		//Castling
		if($p->pieceID == ChessPiece::KING) {
			$castling = self::isCastling($x, $toX);
			if($castling !== false) {
				$hasMoved[$p->colour][ChessPiece::KING] = false;
				$hasMoved[$p->colour][ChessPiece::ROOK][$castling['Type']] = false;
				if($board[$toY][$castling['ToX']] == null) {
					throw new Exception('Cannot undo castle with non-existent castle.');
				}
				$board[$y][$castling['X']] = $board[$toY][$castling['ToX']];
				$board[$y][$castling['X']]->setX($castling['X']);
				$board[$toY][$castling['ToX']] = null;
			}
		}
		else if($moveInfo['EnPassant'] === true) {
			$board[$toY][$toX] = null;
			$board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] = $moveInfo['PieceTaken'];
		}
		else if ($moveInfo['RookMoved'] !== false) {
			$hasMoved[$p->colour][ChessPiece::ROOK][$moveInfo['RookMoved']] = false;
		}
	}

	public function tryMove($x, $y, $toX, $toY, $forAccountID) {
		if($this->getCurrentTurnAccountID() != $forAccountID) {
			return 4;
		}
		$this->getBoard();
		$p = $this->board[$y][$x];
		if($p == null) {
			return 2;
		}
		
		$moves = $p->getPossibleMoves($this->board, $this->getHasMoved(), $forAccountID);
		foreach($moves as $move) {
			if($move[0]==$toX && $move[1]==$toY) {
				$currentPlayer =& $this->getCurrentTurnPlayer();
				
				$moveInfo = ChessGame::movePiece($this->board, $this->getHasMoved(), $x, $y, $toX, $toY);
				
				$pieceTakenID = null;
				if($moveInfo['PieceTaken'] != null) {
					$pieceTakenID = $moveInfo['PieceTaken']->pieceID;
					if($moveInfo['PieceTaken']->pieceID == ChessPiece::KING) {
						throw new Exception('King was taken.');
					}
				}
				
				$checking = null;
				if($p->isAttacking($this->board, $this->getHasMoved(), true)) {
					$checking = 'CHECK';
				}
				if($this->isCheckmated($p->colour == self::PLAYER_WHITE ? self::PLAYER_BLACK : self::PLAYER_WHITE)) {
					$checking = 'MATE';
				}
				if($this->moves!=null) {
					$this->moves[] = $this->createMove($p->pieceID, $x, $y, $toX, $toY, $pieceTakenID, $checking, $this->getCurrentTurnColour(), $moveInfo['Castling']['Type'], $moveInfo['EnPassant']);
				}
				if($p->isChecked($this->board, $this->getHasMoved())) {
					return 3;
				}
				$otherPlayer =& $this->getCurrentTurnPlayer();
				$this->db->query('INSERT INTO chess_game_moves
								(chess_game_id,piece_id,start_x,start_y,end_x,end_y,checked,piece_taken,castling,en_passant)
								VALUES
								(' . $p->gameID . ',' . $p->pieceID . ',' . $x . ',' . $y . ',' . $toX . ',' . $toY . ',' . $this->db->escapeString($checking, true, true) . ',' . ($moveInfo['PieceTaken'] == null ? 'NULL' : $moveInfo['PieceTaken']->pieceID) . ',' . $this->db->escapeString($moveInfo['Castling']['Type'], true, true) . ',' . $this->db->escapeBoolean($moveInfo['EnPassant']) .');');
				
				
				$currentPlayer->increaseHOF(1, array('Chess','Moves','Total Taken'), HOF_PUBLIC);
				if($moveInfo['PieceTaken'] != null) {
					$this->db->query('DELETE FROM chess_game_pieces
									WHERE chess_game_id=' . $this->chessGameID . ' AND account_id=' . $moveInfo['PieceTaken']->accountID . ' AND piece_id=' . $moveInfo['PieceTaken']->pieceID . ' AND piece_no=' . $moveInfo['PieceTaken']->pieceNo . ';');
				
					$pieceTakenSymbol = $moveInfo['PieceTaken']->getPieceSymbol();
					$currentPlayer->increaseHOF(1, array('Chess','Moves','Opponent Pieces Taken','Total'), HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, array('Chess','Moves','Own Pieces Taken','Total'), HOF_PUBLIC);
					$currentPlayer->increaseHOF(1, array('Chess','Moves','Opponent Pieces Taken',$pieceTakenSymbol), HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, array('Chess','Moves','Own Pieces Taken',$pieceTakenSymbol), HOF_PUBLIC);
				}
				$this->db->query('UPDATE chess_game_pieces
							SET x=' . $toX . ', y=' . $toY . '
							WHERE chess_game_id=' . $this->chessGameID . ' AND account_id=' . $p->accountID . ' AND piece_id=' . $p->pieceID . ' AND piece_no=' . $p->pieceNo . ';');
				if($moveInfo['Castling'] !== false) {
					$this->db->query('UPDATE chess_game_pieces
								SET x=' . $moveInfo['Castling']['ToX'] . '
								WHERE chess_game_id=' . $this->chessGameID . ' AND account_id=' . $p->accountID . ' AND x = ' . $moveInfo['Castling']['X'] . ' AND y = ' . $y . ';');
				}
				$return = 0;
				if($checking != null) {
					if($checking == 'MATE') {
						$this->winner = $forAccountID;
						$this->db->query('UPDATE chess_game
										SET end_time=' . TIME . ', winner_id=' . $this->winner . '
										WHERE chess_game_id=' . $this->chessGameID . ';');
						$currentPlayer->increaseHOF(1, array('Chess','Games','Won'), HOF_PUBLIC);
						$otherPlayer->increaseHOF(1, array('Chess','Games','Lost'), HOF_PUBLIC);
						$return = 1;
					}
					else if($checking == 'CHECK') {
						$currentPlayer->increaseHOF(1, array('Chess','Moves','Check Given'), HOF_PUBLIC);
						$otherPlayer->increaseHOF(1, array('Chess','Moves','Check Received'), HOF_PUBLIC);
					}
				}
				$currentPlayer->saveHOF();
				$otherPlayer->saveHOF();
				return $return;
			}
		}
	}

	public function getChessGameID() {
		return $this->chessGameID;
	}

	public function getStartDate() {
		return $this->startDate;
	}

	public function getGameID() {
		return SmrSession::$game_id;
//		return $this->gameID;
	}

	public function &getWhitePlayer() {
		try {
			return SmrPlayer::getPlayer($this->whiteID, $this->getGameID());
		}
		catch(Exception $e) {
			$null = null;
			return $null;
		}
	}

	public function getWhiteID() {
		return $this->whiteID;
	}

	public function &getBlackPlayer() {
		try {
			return SmrPlayer::getPlayer($this->blackID, $this->getGameID());
		}
		catch(Exception $e) {
			$null = null;
			return $null;
		}
	}

	public function getBlackID() {
		return $this->blackID;
	}
	
	public function getColourForAccountID($accountID) {
		return $accountID == $this->getWhiteID() ? self::PLAYER_WHITE : ($accountID == $this->getBlackID() ? self::PLAYER_BLACK : false);
	}

	public function getEndDate() {
		return $this->endDate;
	}

	public function getWinner() {
		return $this->winner;
	}

	public function &getHasMoved() {
		return $this->hasMoved;
	}

	public function getCurrentTurnColour() {
		return count($this->getMoves()) % 2 == 0 ? self::PLAYER_WHITE : self::PLAYER_BLACK;
	}

	public function getCurrentTurnAccountID() {
		return count($this->getMoves()) % 2 == 0 ? $this->whiteID : $this->blackID;
	}

	public function &getCurrentTurnPlayer() {
		try {
		return SmrPlayer::getPlayer($this->getCurrentTurnAccountID(), $this->getGameID());
		}
		catch(Exception $e) {
			$null = null;
			return $null;
		}
	}

	public function isCurrentTurn($accountID) {
		return $accountID == $this->getCurrentTurnAccountID();
	}
	
	public function getPlayGameHREF() {
		return SmrSession::get_new_href(create_container('skeleton.php','chess_play.php',array('ChessGameID' => $this->chessGameID)));
	}
}
?>