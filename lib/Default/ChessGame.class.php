<?php declare(strict_types=1);
/**
 * @author Page
 *
 */
class ChessGame {
	const GAMETYPE_STANDARD = 'Standard';
	const PLAYER_BLACK = 'Black';
	const PLAYER_WHITE = 'White';
	protected static $CACHE_CHESS_GAMES = array();

	private $db;

	private $chessGameID;
	private $gameID;
	private $startDate;
	private $endDate;
	private $winner;
	private $whiteID;
	private $blackID;

	private $hasMoved;
	private $board;
	private $moves;

	private $lastMove = null;

	public static function getNPCMoveGames($forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT chess_game_id
					FROM npc_logins
					JOIN account USING(login)
					JOIN chess_game ON account_id = black_id OR account_id = white_id
					WHERE end_time > ' . TIME . ' OR end_time IS NULL;');
		$games = array();
		while ($db->nextRecord()) {
			$game = self::getChessGame($db->getInt('chess_game_id'), $forceUpdate);
			if ($game->getCurrentTurnAccount()->isNPC()) {
				$games[] = $game;
			}
		}
		return $games;
	}

	public static function getOngoingPlayerGames(AbstractSmrPlayer $player) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT chess_game_id FROM chess_game WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND (black_id = ' . $db->escapeNumber($player->getAccountID()) . ' OR white_id = ' . $db->escapeNumber($player->getAccountID()) . ') AND (end_time > ' . TIME . ' OR end_time IS NULL);');
		$games = array();
		while ($db->nextRecord()) {
			$games[] = self::getChessGame($db->getInt('chess_game_id'));
		}
		return $games;
	}

	public static function getAccountGames($accountID) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT chess_game_id FROM chess_game WHERE black_id = ' . $db->escapeNumber($accountID) . ' OR white_id = ' . $db->escapeNumber($accountID) . ';');
		$games = array();
		while ($db->nextRecord()) {
			$games[] = self::getChessGame($db->getInt('chess_game_id'));
		}
		return $games;
	}

	public static function getChessGame($chessGameID, $forceUpdate = false) {
		if ($forceUpdate || !isset(self::$CACHE_CHESS_GAMES[$chessGameID])) {
			self::$CACHE_CHESS_GAMES[$chessGameID] = new ChessGame($chessGameID);
		}
		return self::$CACHE_CHESS_GAMES[$chessGameID];
	}

	public function __construct($chessGameID) {
		$this->db = new SmrMySqlDatabase();
		$this->db->query('SELECT *
						FROM chess_game
						WHERE chess_game_id=' . $this->db->escapeNumber($chessGameID) . ' LIMIT 1;');
		if ($this->db->nextRecord()) {
			$this->chessGameID = $chessGameID;
			$this->gameID = $this->db->getInt('game_id');
			$this->startDate = $this->db->getInt('start_time');
			$this->endDate = $this->db->getInt('end_time');
			$this->whiteID = $this->db->getInt('white_id');
			$this->blackID = $this->db->getInt('black_id');
			$this->winner = $this->db->getInt('winner_id');
			$this->resetHasMoved();
		} else {
			throw new Exception('Chess game not found: ' . $chessGameID);
		}
	}

	public static function isValidCoord($x, $y, array &$board) {
		return $y < count($board) && $y >= 0 && $x < count($board[$y]) && $x >= 0;
	}

	public static function isPlayerChecked(array &$board, array &$hasMoved, $colour) {
		foreach ($board as &$row) {
			foreach ($row as &$p) {
				if ($p != null && $p->colour != $colour && $p->isAttacking($board, $hasMoved, true)) {
					return true;
				}
			}
		}
		return false;
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
			ChessPiece::PAWN => array(-1, -1)
		);
	}

	public function rerunGame($debugInfo = false) {
		$db = new SmrMySqlDatabase();
		$db2 = new SmrMySqlDatabase();

		$db->query('UPDATE chess_game
					SET end_time = NULL, winner_id = 0
					WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ';');
		$db->query('DELETE FROM chess_game_pieces WHERE chess_game_id = ' . $this->db->escapeNumber($this->chessGameID) . ';');
		self::insertPieces($this->chessGameID, $this->getWhitePlayer(), $this->getBlackPlayer());

		$db->query('SELECT * FROM chess_game_moves WHERE chess_game_id = ' . $this->db->escapeNumber($this->chessGameID) . ' ORDER BY move_id;');
		$db2->query('DELETE FROM chess_game_moves WHERE chess_game_id = ' . $this->db->escapeNumber($this->chessGameID) . ';');
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
		} catch(Exception $e) {
			if($debugInfo === true) {
				echo $e->getMessage() . EOL . $e->getTraceAsString() . EOL;
			}
			// We probably tried an invalid move - move on.
		}
	}

	public function getBoard() {
		if ($this->board == null) {
			$this->db->query('SELECT * FROM chess_game_pieces WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ';');
			$pieces = array();
			while ($this->db->nextRecord()) {
				$accountID = $this->db->getInt('account_id');
				$pieces[] = new ChessPiece($this->chessGameID, $accountID, $this->getColourForAccountID($accountID), $this->db->getInt('piece_id'), $this->db->getInt('x'), $this->db->getInt('y'), $this->db->getInt('piece_no'));
			}
			$this->board = $this->parsePieces($pieces);
		}
		return $this->board;
	}

	public function getLastMove() {
		$this->getMoves();
		return $this->lastMove;
	}

	public function getMoves() {
		if($this->moves == null) {
			$this->db->query('SELECT * FROM chess_game_moves WHERE chess_game_id = ' . $this->db->escapeNumber($this->chessGameID) . ' ORDER BY move_id;');
			$this->moves = array();
			$mate = false;
			while($this->db->nextRecord()) {
				$pieceTakenID = $this->db->getField('piece_taken') == null ? null : $this->db->getInt('piece_taken');
				$this->moves[] = $this->createMove($this->db->getInt('piece_id'), $this->db->getInt('start_x'), $this->db->getInt('start_y'), $this->db->getInt('end_x'), $this->db->getInt('end_y'), $pieceTakenID, $this->db->getField('checked'), $this->db->getInt('move_id') % 2 == 1 ? self::PLAYER_WHITE : self::PLAYER_BLACK, $this->db->getField('castling'), $this->db->getBoolean('en_passant'), $this->db->getInt('promote_piece_id'));
				$mate = $this->db->getField('checked') == 'MATE';
			}
			if(!$mate && $this->hasEnded()) {
				if($this->getWinner() != 0) {
					$this->moves[] = ($this->getWinner() == $this->getWhiteID() ? 'Black' : 'White') . ' Resigned.';
				} else if(count($this->moves) < 2) {
					$this->moves[] = 'Game Cancelled.';
				} else {
					$this->moves[] = 'Game Drawn.';
				}
			}
		}
		return $this->moves;
	}

	public function getFENString() {
		$fen = '';
		$board =& $this->getBoard();
		$blanks = 0;
		for($y=0; $y < 8; $y++) {
			if($y > 0) {
				$fen .= '/';
			}
			for($x=0; $x < 8; $x++) {
				if($board[$y][$x] == null) {
					$blanks++;
				} else {
					if($blanks > 0) {
						$fen .= $blanks;
						$blanks = 0;
					}
					$fen .= $board[$y][$x]->getPieceLetter();
				}
			}
			if($blanks > 0) {
				$fen .= $blanks;
				$blanks = 0;
			}
		}
		switch($this->getCurrentTurnColour()) {
			case self::PLAYER_WHITE:
				$fen .= ' w ';
			break;
			case self::PLAYER_BLACK:
				$fen .= ' b ';
			break;
		}

		// Castling
		$castling = '';
		if ($this->hasMoved[self::PLAYER_WHITE][ChessPiece::KING] !== true) {
			if ($this->hasMoved[self::PLAYER_WHITE][ChessPiece::ROOK]['King'] !== true) {
				$castling .= 'K';
			}
			if ($this->hasMoved[self::PLAYER_WHITE][ChessPiece::ROOK]['Queen'] !== true) {
				$castling .= 'Q';
			}
		}
		if ($this->hasMoved[self::PLAYER_BLACK][ChessPiece::KING] !== true) {
			if ($this->hasMoved[self::PLAYER_BLACK][ChessPiece::ROOK]['King'] !== true) {
				$castling .= 'k';
			}
			if ($this->hasMoved[self::PLAYER_BLACK][ChessPiece::ROOK]['Queen'] !== true) {
				$castling .= 'q';
			}
		}
		if ($castling == '') {
			$castling = '-';
		}
		$fen .= $castling . ' ';

		if ($this->hasMoved[ChessPiece::PAWN][0] != -1) {
			$fen .= chr(ord('a') + $this->hasMoved[ChessPiece::PAWN][0]);
			switch ($this->hasMoved[ChessPiece::PAWN][1]) {
				case 3:
					$fen .= '6';
				break;
				case 4:
					$fen .= '3';
				break;
			}
		} else {
			$fen .= '-';
		}
		$fen .= ' 0 ' . floor(count($this->moves) / 2);

		return $fen;
	}

	private static function parsePieces(array $pieces) {
		$board = array();
		$row = array();
		for ($i = 0; $i < 8; $i++) {
			$row[] = null;
		}
		for ($i = 0; $i < 8; $i++) {
			$board[] = $row;
		}
		foreach ($pieces as $piece) {
			if ($board[$piece->getY()][$piece->getX()] != null) {
				throw new Exception('Two pieces found in the same tile.');
			}
			$board[$piece->getY()][$piece->getX()] = $piece;
		}
		return $board;
	}

	public static function getStandardGame($chessGameID, AbstractSmrPlayer $whitePlayer, AbstractSmrPlayer $blackPlayer) {
		$white = $whitePlayer->getAccountID();
		$black = $blackPlayer->getAccountID();
		return array(
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::ROOK, 0, 0),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::KNIGHT, 1, 0),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::BISHOP, 2, 0),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::QUEEN, 3, 0),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::KING, 4, 0),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::BISHOP, 5, 0),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::KNIGHT, 6, 0),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::ROOK, 7, 0),

				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 0, 1),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 1, 1),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 2, 1),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 3, 1),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 4, 1),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 5, 1),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 6, 1),
				new ChessPiece($chessGameID, $black, self::PLAYER_BLACK, ChessPiece::PAWN, 7, 1),

				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 0, 6),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 1, 6),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 2, 6),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 3, 6),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 4, 6),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 5, 6),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 6, 6),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::PAWN, 7, 6),

				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::ROOK, 0, 7),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::KNIGHT, 1, 7),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::BISHOP, 2, 7),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::QUEEN, 3, 7),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::KING, 4, 7),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::BISHOP, 5, 7),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::KNIGHT, 6, 7),
				new ChessPiece($chessGameID, $white, self::PLAYER_WHITE, ChessPiece::ROOK, 7, 7),
			);
	}

	public static function insertNewGame($startDate, $endDate, AbstractSmrPlayer $whitePlayer, AbstractSmrPlayer $blackPlayer) {
		if ($startDate == null) {
			throw new Exception('Start date cannot be null.');
		}

		$db = new SmrMySqlDatabase();
		$db->query('INSERT INTO chess_game' .
				'(start_time,end_time,white_id,black_id,game_id)' .
				'values' .
				'(' . $db->escapeNumber($startDate) . ',' . ($endDate == null ? 'NULL' : $db->escapeNumber($endDate)) . ',' . $db->escapeNumber($whitePlayer->getAccountID()) . ',' . $db->escapeNumber($blackPlayer->getAccountID()) . ',' . $db->escapeNumber($whitePlayer->getGameID()) . ');');
		$chessGameID = $db->getInsertID();

		self::insertPieces($chessGameID, $whitePlayer, $blackPlayer);
		return $chessGameID;
	}

	private static function insertPieces($chessGameID, AbstractSmrPlayer $whitePlayer, AbstractSmrPlayer $blackPlayer) {
		$db = new SmrMySqlDatabase();
		$pieces = self::getStandardGame($chessGameID, $whitePlayer, $blackPlayer);
		foreach ($pieces as $p) {
			$db->query('INSERT INTO chess_game_pieces' .
			'(chess_game_id,account_id,piece_id,x,y)' .
			'values' .
			'(' . $db->escapeNumber($chessGameID) . ',' . $db->escapeNumber($p->accountID) . ',' . $db->escapeNumber($p->pieceID) . ',' . $db->escapeNumber($p->getX()) . ',' . $db->escapeNumber($p->getY()) . ');');
		}
	}

	private function createMove($pieceID, $startX, $startY, $endX, $endY, $pieceTaken, $checking, $playerColour, $castling, $enPassant, $promotionPieceID) {
		// This move will be set as the most recent move
		$this->lastMove = [
			'From' => ['X' => $startX, 'Y' => $startY],
			'To'   => ['X' => $endX,   'Y' => $endY],
		];

		$otherPlayerColour = self::getOtherColour($playerColour);
		if($pieceID == ChessPiece::KING) {
			$this->hasMoved[$playerColour][ChessPiece::KING] = true;
		}
		// Check if the piece moving is a rook and mark it as moved to stop castling.
		if($pieceID == ChessPiece::ROOK && ($startX == 0 || $startX == 7) && ($startY == ($playerColour == self::PLAYER_WHITE ? 7 : 0))) {
			$this->hasMoved[$playerColour][ChessPiece::ROOK][$startX==0?'Queen':'King'] = true;
		}
		// Check if we've taken a rook and marked them as moved, if they've already moved this does nothing, but if they were taken before moving this stops an issue with trying to castle with a non-existent castle.
		if($pieceTaken == ChessPiece::ROOK && ($endX == 0 || $endX == 7) && $endY == ($otherPlayerColour == self::PLAYER_WHITE ? 7 : 0)) {
			$this->hasMoved[$otherPlayerColour][ChessPiece::ROOK][$endX==0?'Queen':'King'] = true;
		}
		if($pieceID == ChessPiece::PAWN && ($startY == 1 || $startY == 6) && ($endY == 3 || $endY == 4)) {
			$this->hasMoved[ChessPiece::PAWN] = array($endX, $endY);
		} else {
			$this->hasMoved[ChessPiece::PAWN] = array(-1,-1);
		}
		return ($castling == 'Queen' ? '0-0-0' : ($castling == 'King' ? '0-0' : ''))
			. ChessPiece::getSymbolForPiece($pieceID, $playerColour)
			. chr(ord('a')+$startX)
			. (8-$startY)
			. ' '
			. ($pieceTaken == null ? '' : ChessPiece::getSymbolForPiece($pieceTaken, $otherPlayerColour))
			. chr(ord('a')+$endX)
			. (8-$endY)
			. ($promotionPieceID == null ? '' : ChessPiece::getSymbolForPiece($promotionPieceID, $playerColour))
			. ' '
			. ($checking == null ? '' : ($checking == 'CHECK' ? '+' : '++') )
			. ($enPassant ? ' e.p.' : '');
	}

	public function isCheckmated($colour) {
		$king = null;
		foreach($this->board as $row) {
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
		if(!self::isPlayerChecked($this->board, $this->getHasMoved(), $colour)) {
			return false;
		}
		foreach($this->board as $row) {
			foreach($row as $piece) {
				if($piece != null && $piece->colour == $colour) {
					$moves = $piece->getPossibleMoves($this->board, $this->getHasMoved());
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
		if (abs($movement) == 2) {
			//To the left.
			if ($movement == -2) {
				return array('Type' => 'Queen',
						'X' => 0,
						'ToX' => 3
					);
			} //To the right
			else if ($movement == 2) {
				return array('Type' => 'King',
						'X' => 7,
						'ToX' => 5
					);
			}
		}
		return false;
	}

	public static function movePiece(array &$board, array &$hasMoved, $x, $y, $toX, $toY, $pawnPromotionPiece = ChessPiece::QUEEN) {
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
		$rookTaken = false;
		$pawnPromotion = false;
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
		} else if($p->pieceID == ChessPiece::PAWN) {
			if($toY == 0 || $toY == 7) {
				$pawnPromotion = $p->promote($pawnPromotionPiece, $board);
			}
			//Double move to track?
			else if(($y == 1 || $y == 6) && ($toY == 3 || $toY == 4)) {
				$nextPawnMovement = array($toX, $toY);
			}
			//En passant?
			else if($hasMoved[ChessPiece::PAWN][0] == $toX &&
					($hasMoved[ChessPiece::PAWN][1] == 3 && $toY == 2 || $hasMoved[ChessPiece::PAWN][1] == 4 && $toY == 5)) {
				$enPassant = true;
				$pieceTaken = $board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]];
				if($board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] == null) {
					throw new Exception('Cannot en passant a non-existent pawn.');
				}
				$board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] = null;
			}
		} else if($p->pieceID == ChessPiece::ROOK && ($x == 0 || $x == 7) && $y == ($p->colour == self::PLAYER_WHITE ? 7 : 0)) {
			//Rook moved?
			if($hasMoved[$p->colour][ChessPiece::ROOK][$x==0?'Queen':'King'] === false) {
				// We set rook moved in here as it's used for move info.
				$rookMoved = $x==0?'Queen':'King';
				$hasMoved[$p->colour][ChessPiece::ROOK][$rookMoved] = true;
			}
		}
		// Check if we've taken a rook and marked them as moved, if they've already moved this does nothing, but if they were taken before moving this stops an issue with trying to castle with a non-existent castle.
		if($pieceTaken != null && $pieceTaken->pieceID == ChessPiece::ROOK && ($toX == 0 || $toX == 7) && $toY == ($pieceTaken->colour == self::PLAYER_WHITE ? 7 : 0)) {
			if($hasMoved[$pieceTaken->colour][ChessPiece::ROOK][$toX==0?'Queen':'King'] === false) {
				$rookTaken = $toX==0?'Queen':'King';
				$hasMoved[$pieceTaken->colour][ChessPiece::ROOK][$rookTaken] = true;
			}
		}
		
		$hasMoved[ChessPiece::PAWN] = $nextPawnMovement;
		return array('Castling' => $castling,
				'PieceTaken' => $pieceTaken,
				'EnPassant' => $enPassant,
				'RookMoved' => $rookMoved,
				'RookTaken' => $rookTaken,
				'OldPawnMovement' => $oldPawnMovement,
				'PawnPromotion' => $pawnPromotion
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
		if ($p->pieceID == ChessPiece::KING) {
			$castling = self::isCastling($x, $toX);
			if ($castling !== false) {
				$hasMoved[$p->colour][ChessPiece::KING] = false;
				$hasMoved[$p->colour][ChessPiece::ROOK][$castling['Type']] = false;
				if ($board[$toY][$castling['ToX']] == null) {
					throw new Exception('Cannot undo castle with non-existent castle.');
				}
				$board[$y][$castling['X']] = $board[$toY][$castling['ToX']];
				$board[$y][$castling['X']]->setX($castling['X']);
				$board[$toY][$castling['ToX']] = null;
			}
		} else if($moveInfo['EnPassant'] === true) {
			$board[$toY][$toX] = null;
			$board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] = $moveInfo['PieceTaken'];
		} else if($moveInfo['RookMoved'] !== false) {
			$hasMoved[$p->colour][ChessPiece::ROOK][$moveInfo['RookMoved']] = false;
		}
		if($moveInfo['RookTaken'] !== false) {
			$hasMoved[$moveInfo['PieceTaken']->colour][ChessPiece::ROOK][$moveInfo['RookTaken']] = false;
		}
	}

	public function tryAlgebraicMove($move) {
		if(strlen($move) != 4 && strlen($move) != 5) {
			throw new Exception('Move of length "' . strlen($move) . '" is not valid, full move: ' . $move);
		}
		$aVal = ord('a');
		$hVal = ord('h');
		if(ord($move[0]) < $aVal || ord($move[2]) < $aVal
				|| ord($move[0]) > $hVal || ord($move[2]) > $hVal
				|| $move[1] < 1 || $move[3] < 1
				|| $move[1] > 8 || $move[3] > 8) {
			throw new Exception('Invalid move: ' . $move);
		}
		$x = ord($move[0]) - $aVal;
		$y = 8 - $move[1];
		$toX = ord($move[2]) - $aVal;
		$toY = 8 - $move[3];
		$pawnPromotionPiece = null;
		if(isset($move[4])) {
			$pawnPromotionPiece = ChessPiece::getPieceForLetter($move[4]);
		}
		return $this->tryMove($x, $y, $toX, $toY, $this->getCurrentTurnAccountID(), $pawnPromotionPiece);
	}

	public function tryMove($x, $y, $toX, $toY, $forAccountID, $pawnPromotionPiece) {
		if($this->hasEnded()) {
			return 5;
		}
		if($this->getCurrentTurnAccountID() != $forAccountID) {
			return 4;
		}
		$lastTurnPlayer = $this->getCurrentTurnPlayer();
		$this->getBoard();
		$p = $this->board[$y][$x];
		if($p == null || $p->colour != $this->getColourForAccountID($forAccountID)) {
			return 2;
		}

		$moves = $p->getPossibleMoves($this->board, $this->getHasMoved(), $forAccountID);
		foreach($moves as $move) {
			if($move[0]==$toX && $move[1]==$toY) {
				$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
				$currentPlayer = $this->getCurrentTurnPlayer();

				$moveInfo = ChessGame::movePiece($this->board, $this->getHasMoved(), $x, $y, $toX, $toY, $pawnPromotionPiece);

				//We have taken the move, we should refresh $p
				$p =& $this->board[$toY][$toX];

				$pieceTakenID = null;
				if($moveInfo['PieceTaken'] != null) {
					$pieceTakenID = $moveInfo['PieceTaken']->pieceID;
					if($moveInfo['PieceTaken']->pieceID == ChessPiece::KING) {
						throw new Exception('King was taken.');
					}
				}

				$pieceID = $p->pieceID;
				$pieceNo = $p->pieceNo;
				if($moveInfo['PawnPromotion'] !== false) {
					$p->pieceID = $moveInfo['PawnPromotion']['PieceID'];
					$p->pieceNo = $moveInfo['PawnPromotion']['PieceNo'];
				}

				$checking = null;
				if($p->isAttacking($this->board, $this->getHasMoved(), true)) {
					$checking = 'CHECK';
				}
				if($this->isCheckmated(self::getOtherColour($p->colour))) {
					$checking = 'MATE';
				}
				if($this->moves!=null) {
					$this->moves[] = $this->createMove($pieceID, $x, $y, $toX, $toY, $pieceTakenID, $checking, $this->getCurrentTurnColour(), $moveInfo['Castling']['Type'], $moveInfo['EnPassant'], $moveInfo['PawnPromotion'] === false ? null : $moveInfo['PawnPromotion']['PieceID']);
				}
				if(self::isPlayerChecked($this->board, $this->getHasMoved(), $p->colour)) {
					return 3;
				}

				$otherPlayer = $this->getCurrentTurnPlayer();
				if($moveInfo['PawnPromotion'] !== false) {
					$piecePromotedSymbol = $p->getPieceSymbol();
					$currentPlayer->increaseHOF(1, array($chessType,'Moves','Own Pawns Promoted','Total'), HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, array($chessType,'Moves','Opponent Pawns Promoted','Total'), HOF_PUBLIC);
					$currentPlayer->increaseHOF(1, array($chessType,'Moves','Own Pawns Promoted',$piecePromotedSymbol), HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, array($chessType,'Moves','Opponent Pawns Promoted',$piecePromotedSymbol), HOF_PUBLIC);
				}

				$castlingType = $moveInfo['Castling'] === false ? null : $moveInfo['Castling']['Type'];
				$this->db->query('INSERT INTO chess_game_moves
								(chess_game_id,piece_id,start_x,start_y,end_x,end_y,checked,piece_taken,castling,en_passant,promote_piece_id)
								VALUES
								(' . $this->db->escapeNumber($p->chessGameID) . ',' . $this->db->escapeNumber($pieceID) . ',' . $this->db->escapeNumber($x) . ',' . $this->db->escapeNumber($y) . ',' . $this->db->escapeNumber($toX) . ',' . $this->db->escapeNumber($toY) . ',' . $this->db->escapeString($checking, true, true) . ',' . ($moveInfo['PieceTaken'] == null ? 'NULL' : $this->db->escapeNumber($moveInfo['PieceTaken']->pieceID)) . ',' . $this->db->escapeString($castlingType, true, true) . ',' . $this->db->escapeBoolean($moveInfo['EnPassant']) . ',' . ($moveInfo['PawnPromotion'] == false ? 'NULL' : $this->db->escapeNumber($moveInfo['PawnPromotion']['PieceID'])) . ');');


				$currentPlayer->increaseHOF(1, array($chessType,'Moves','Total Taken'), HOF_PUBLIC);
				if($moveInfo['PieceTaken'] != null) {
					$this->db->query('DELETE FROM chess_game_pieces
									WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ' AND account_id=' . $this->db->escapeNumber($moveInfo['PieceTaken']->accountID) . ' AND piece_id=' . $this->db->escapeNumber($moveInfo['PieceTaken']->pieceID) . ' AND piece_no=' . $this->db->escapeNumber($moveInfo['PieceTaken']->pieceNo) . ';');

					$pieceTakenSymbol = $moveInfo['PieceTaken']->getPieceSymbol();
					$currentPlayer->increaseHOF(1, array($chessType,'Moves','Opponent Pieces Taken','Total'), HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, array($chessType,'Moves','Own Pieces Taken','Total'), HOF_PUBLIC);
					$currentPlayer->increaseHOF(1, array($chessType,'Moves','Opponent Pieces Taken',$pieceTakenSymbol), HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, array($chessType,'Moves','Own Pieces Taken',$pieceTakenSymbol), HOF_PUBLIC);
				}
				$this->db->query('UPDATE chess_game_pieces
							SET x=' . $this->db->escapeNumber($toX) . ', y=' . $this->db->escapeNumber($toY) .
								($moveInfo['PawnPromotion'] !== false ? ', piece_id=' . $this->db->escapeNumber($moveInfo['PawnPromotion']['PieceID']) . ', piece_no=' . $this->db->escapeNumber($moveInfo['PawnPromotion']['PieceNo']) : '') . '
							WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ' AND account_id=' . $this->db->escapeNumber($p->accountID) . ' AND piece_id=' . $this->db->escapeNumber($pieceID) . ' AND piece_no=' . $this->db->escapeNumber($pieceNo) . ';');
				if($moveInfo['Castling'] !== false) {
					$this->db->query('UPDATE chess_game_pieces
								SET x=' . $this->db->escapeNumber($moveInfo['Castling']['ToX']) . '
								WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ' AND account_id=' . $this->db->escapeNumber($p->accountID) . ' AND x = ' . $this->db->escapeNumber($moveInfo['Castling']['X']) . ' AND y = ' . $this->db->escapeNumber($y) . ';');
				}
				$return = 0;
				if($checking == 'MATE') {
					$this->setWinner($forAccountID);
					$return = 1;
					SmrPlayer::sendMessageFromCasino($lastTurnPlayer->getGameID(), $this->getCurrentTurnAccountID(), 'You have just lost [chess=' . $this->getChessGameID() . '] against [player=' . $lastTurnPlayer->getPlayerID() . '].');
				} else {
					SmrPlayer::sendMessageFromCasino($lastTurnPlayer->getGameID(), $this->getCurrentTurnAccountID(), 'It is now your turn in [chess=' . $this->getChessGameID() . '] against [player=' . $lastTurnPlayer->getPlayerID() . '].');
					if($checking == 'CHECK') {
						$currentPlayer->increaseHOF(1, array($chessType,'Moves','Check Given'), HOF_PUBLIC);
						$otherPlayer->increaseHOF(1, array($chessType,'Moves','Check Received'), HOF_PUBLIC);
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
		return $this->gameID;
	}

	public function getWhitePlayer() {
		return SmrPlayer::getPlayer($this->whiteID, $this->getGameID());
	}

	public function getWhiteID() {
		return $this->whiteID;
	}

	public function getBlackPlayer() {
		return SmrPlayer::getPlayer($this->blackID, $this->getGameID());
	}

	public function getBlackID() {
		return $this->blackID;
	}

	public function getColourID($colour) {
		if ($colour == self::PLAYER_WHITE) {
			return $this->getWhiteID();
		}
		if ($colour == self::PLAYER_BLACK) {
			return $this->getBlackID();
		}
	}

	public function getColourPlayer($colour) {
		return SmrPlayer::getPlayer($this->getColourID($colour), $this->getGameID());
	}

	public function getColourForAccountID($accountID) {
		if ($accountID == $this->getWhiteID()) {
			return self::PLAYER_WHITE;
		}
		if ($accountID == $this->getBlackID()) {
			return self::PLAYER_BLACK;
		}
		return false;
	}

	public function getEndDate() {
		return $this->endDate;
	}

	public function hasEnded() {
		return $this->endDate != 0 && $this->endDate <= TIME;
	}

	public function getWinner() {
		return $this->winner;
	}

	public function setWinner($accountID) {
		$this->winner = $accountID;
		$this->endDate = TIME;
		$this->db->query('UPDATE chess_game
						SET end_time=' . $this->db->escapeNumber(TIME) . ', winner_id=' . $this->db->escapeNumber($this->winner) . '
						WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ';');
		$winnerColour = $this->getColourForAccountID($accountID);
		$winningPlayer = $this->getColourPlayer($winnerColour);
		$losingPlayer = $this->getColourPlayer(self::getOtherColour($winnerColour));
		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$winningPlayer->increaseHOF(1, array($chessType, 'Games', 'Won'), HOF_PUBLIC);
		$losingPlayer->increaseHOF(1, array($chessType, 'Games', 'Lost'), HOF_PUBLIC);
		return array('Winner' => $winningPlayer, 'Loser' => $losingPlayer);
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

	public function getCurrentTurnPlayer() {
		return SmrPlayer::getPlayer($this->getCurrentTurnAccountID(), $this->getGameID());
	}

	public function getCurrentTurnAccount() {
		return SmrAccount::getAccount($this->getCurrentTurnAccountID());
	}

	public function getWhiteAccount() {
		return SmrAccount::getAccount($this->getWhiteID());
	}

	public function getBlackAccount() {
		return SmrAccount::getAccount($this->getBlackID());
	}

	public function isCurrentTurn($accountID) {
		return $accountID == $this->getCurrentTurnAccountID();
	}

	public function isNPCGame() {
		return $this->getWhiteAccount()->isNPC() || $this->getBlackAccount()->isNPC();
	}

	public static function getOtherColour($colour) {
		if ($colour == self::PLAYER_WHITE) {
			return self::PLAYER_BLACK;
		}
		if ($colour == self::PLAYER_BLACK) {
			return self::PLAYER_WHITE;
		}
		return false;
	}

	public function resign($accountID) {
		if ($this->hasEnded() || !$this->getColourForAccountID($accountID)) {
			return false;
		}
		// If only 1 person has moved then just end the game.
		if (count($this->getMoves()) < 2) {
			$this->endDate = TIME;
			$this->db->query('UPDATE chess_game
							SET end_time=' . $this->db->escapeNumber(TIME) . '
							WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ';');
			return 1;
		} else {
			$loserColour = $this->getColourForAccountID($accountID);
			$winnerAccountID = $this->getColourID(self::getOtherColour($loserColour));
			$results = $this->setWinner($winnerAccountID);
			$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
			$results['Loser']->increaseHOF(1, array($chessType, 'Games', 'Resigned'), HOF_PUBLIC);
			SmrPlayer::sendMessageFromCasino($results['Winner']->getGameID(), $results['Winner']->getPlayerID(), '[player=' . $results['Loser']->getPlayerID() . '] just resigned against you in [chess=' . $this->getChessGameID() . '].');
			return 0;
		}
	}

	public function getPlayGameHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'chess_play.php', array('ChessGameID' => $this->chessGameID)));
	}

	public function getResignHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'chess_resign_processing.php', array('ChessGameID' => $this->chessGameID)));
	}
}
