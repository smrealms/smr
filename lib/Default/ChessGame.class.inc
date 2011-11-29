<?php
require_once('ChessPiece.class.inc');
/**
 * @author Page
 *
 */
class ChessGame
{
	const GAMETYPE_STANDARD = 'Standard';
	const PLAYER_BLACK = 'Black';
	const PLAYER_WHITE = 'White';

	private $db;
	
	private $chessGameID;
	private $startDate;
	private $endDate;
	private $gameType;
	private $winner;
	
	private $players = array();

	private $hasMoved = array(
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
		)
	);
	private $board;
	private $moves;
	private $messages;
	
	public function __construct($chessGameID)
	{
		$this->db = new SmrMySqlDatabase();
		$this->db->query('SELECT *' .
				' FROM chess_game' .
				' WHERE chess_game_id=' . $chessGameID . ' LIMIT 1;');
		if($this->db->nextRecord())
		{
			$this->chessGameID = $chessGameID;
			$this->startDate = $this->db->getInt('start_time');
			$this->endDate = $this->db->getInt('end_time');
			$this->whiteID = $this->db->getInt('white_id');
			$this->blackID = $this->db->getInt('black_id');
			$this->winner = $this->db->getInt('winner_id');
		}
		else
		{
			throw new Exception('Chess game not found: ' . $chessGameID);
		}
	}
	
	public function getBoard()
	{
		if($this->board == null)
		{
			$this->db->query('SELECT * FROM chess_game_pieces WHERE chess_game_id=' . $this->chessGameID . ';');
			$pieces = array();
			while($this->db->nextRecord())
			{
				$accountID = $this->db->getInt('account_id');
				$pieces[] = new ChessPiece($this->chessGameID, $accountID, $this->getColourForAccountID($accountID), $this->db->getInt('piece_id'), $this->db->getInt('x'), $this->db->getInt('y'), $this->db->getInt('piece_no'));
			}
			$this->board = $this->parsePieces($pieces);
		}
		return $this->board;
	}
	
	public function getMoves()
	{
		if($this->moves == null)
		{
			$this->db->query('SELECT * FROM chess_game_moves WHERE chess_game_id = ' . $this->chessGameID . ' ORDER BY move_id;');
			$this->moves = array();
			while($this->db->nextRecord())
			{
				$pieceTakenID = $this->db->getField('piece_taken') == null ? null : $this->db->getInt('piece_taken');
				$this->moves[] = $this->createMove($this->db->getInt('piece_id'), $this->db->getInt('start_x'), $this->db->getInt('start_y'), $this->db->getInt('end_x'), $this->db->getInt('end_y'), $pieceTakenID, $this->db->getField('checked'), $this->db->getInt('move_id') % 2 == 1 ? self::PLAYER_WHITE : self::PLAYER_BLACK, $this->db->getField('castling'));
			}
		}
		return $this->moves;
	}
	
	public function getMessages()
	{
		if($this->messages == null)
		{
			$df = new SimpleDateFormat('yyyy-MM-dd HH:mm:ss');
			$this->db->query('SELECT * FROM chess_game_messages WHERE chess_game_id = ' . $this->chessGameID . ' ORDER BY message_id;');
			$this->messages = array();
			while($this->db->nextRecord())
			{
				$this->messages[] = $df->format(new Date($this->db->getInt('sent_time'))) . ' ' . $this->db->getField('sender') . ': ' . $this->db->getField('message');
			}
		}
		return $this->messages;
	}
	
	public function addMessage($accountID, $message)
	{
		$this->getMessages();
		$this->db->query('INSERT INTO chess_game_messages' .
				'(chess_game_id,sender,sent_time,message)' .
				'values' .
				'(' . $gameID . ',' . $accountID . ',' . TIME . ',' . $message . ');');
		$this->messages[] = $message;
	}
	
	public static function parsePieces($pieces)
	{
		$board = array();
		$row = array();
		for($i=0;$i<8;$i++)
		{
			$row[] = null;
		}
		for($i=0;$i<8;$i++)
		{
			$board[] = $row;
		}
		foreach($pieces as $piece)
		{
			if($board[$piece->getY()][$piece->getX()] != null)
				throw new Exception('Two pieces found in the same tile.');
			$board[$piece->getY()][$piece->getX()] = $piece;
		}
		return $board;
	}

	public static function getStandardGame($gameID, AbstractSmrPlayer &$whitePlayer, AbstractSmrPlayer &$blackPlayer)
	{
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
	
	public static function insertNewGame($startDate, $endDate, AbstractSmrPlayer &$whitePlayer, AbstractSmrPlayer &$blackPlayer)
	{
		if($startDate == null)
			throw new RuntimeException('Start date cannot be null.');
		
		$db = new SmrMySqlDatabase();
		$db->query('INSERT INTO chess_game' .
				'(start_time,end_time,white_id,black_id)' .
				'values' .
				'(' . $db->escapeNumber($startDate) . ',' . ($endDate == null ? 'NULL' : $db->escapeNumber($endDate)) . ',' . $db->escapeNumber($whitePlayer->getAccountID()) . ',' . $db->escapeNumber($blackPlayer->getAccountID()) . ');');
		$chessGameID = $db->getInsertID();

		$pieces = self::getStandardGame($chessGameID, $whitePlayer, $blackPlayer);
		foreach($pieces as $p)
		{
			$db->query('INSERT INTO chess_game_pieces' .
			'(chess_game_id,account_id,piece_id,x,y)' .
			'values' .
			'(' . $db->escapeNumber($chessGameID) . ',' . $db->escapeNumber($p->accountID) . ',' . $db->escapeNumber($p->pieceID) . ',' . $db->escapeNumber($p->getX()) . ',' . $db->escapeNumber($p->getY()) . ');');
		}
		return $chessGameID;
	}

	public static function &getOngoingAccountGames($accountID)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT chess_game_id FROM chess_game WHERE (black_id = ' . $db->escapeNumber($accountID) . ' OR white_id = ' . $db->escapeNumber($accountID) . ') AND (end_time > ' . TIME . ' OR end_time IS NULL);');
		$games = array();
		while($db->nextRecord())
		{
			$games[] = new ChessGame($db->getInt('chess_game_id'));
		}
		return $games;
	}

	public static function &getAccountGames($accountID)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT chess_game_id FROM chess_game WHERE black_id = ' . $db->escapeNumber($accountID) . ' OR white_id = ' . $db->escapeNumber($accountID) . ';');
		$games = array();
		while($db->nextRecord())
		{
			$games[] = new ChessGame($db->getInt('chess_game_id'));
		}
		return $games;
	}
	
	public function createMove($pieceID, $startX, $startY, $endX, $endY, $pieceTaken, $checking, $playerColour, $castling)
	{
		if($pieceID == ChessPiece::KING)
			$this->hasMoved[$playerColour][ChessPiece::KING] = true;
		if($pieceID == ChessPiece::ROOK && ($startX == 0 || $startX = 7) && ($startY == 0 || $startY == 7))
			$this->hasMoved[$playerColour][ChessPiece::ROOK][$startX==0?'Queen':'King'] = true;
		return ($castling == 'Queen' ? '0-0-0' : $castling == 'King' ? '0-0' : '')
			. ChessPiece::getSymbolForPiece($pieceID, $playerColour)
			. chr(ord('a')+$startX)
			. (8-$startY)
			. ' '
			. ($pieceTaken == null ? '' : ChessPiece::getSymbolForPiece($pieceTaken, $playerColour))
			. chr(ord('a')+$endX)
			. (8-$endY)
			. ' '
			. ($checking == null ? '' : ($checking  == 'CHECK' ? '+' : '++') );
	}
	
	public function isCheckmated($colour)
	{
		$board = $this->getBoard();
		$king = null;
		foreach($board as $row)
		{
			foreach($row as $piece)
			{
				if($piece != null && $piece->pieceID == ChessPiece::KING && $piece->colour == $colour)
				{
					$king = $piece;
					break;
				}
			}
		}
		if($king==null)
		{
			throw new Exception('Could not find the king: game id = ' . $id);
		}
		if(!$king->isChecked($board, $this->getHasMoved()))
		{
			return false;
		}
		foreach($board as $row)
		{
			foreach($row as $piece)
			{
				if($piece != null && $piece->colour == $colour)
				{
					$x = $piece->getX();
					$y = $piece->getY();
					$moves = $piece->getPossibleMoves($board, $this->getHasMoved());
					$board[$y][$x] = null;
					foreach($moves as $move)
					{
						$takenPiece = $board[$move[1]][$move[0]];
						$piece->setX($move[0]);
						$piece->setY($move[1]);
						$board[$move[1]][$move[0]] = $piece;
						if(!$king->isChecked($board, $this->getHasMoved()))
						{
							return false;
						}
						$board[$move[1]][$move[0]] = $takenPiece;
					}
					$piece->setX($x);
					$piece->setY($y);
					$board[$y][$x] = $piece;
				}
			}
		}
		return true;
	}

	public function tryMove($x, $y, $toX, $toY, $forAccountID)
	{
		if($this->getCurrentTurnAccountID() != $forAccountID)
		{
			return 4;
		}
		$this->getBoard();
		$p = $this->board[$y][$x];
		if($p == null)
		{
			return 2;
		}
		$moves = $p->getPossibleMoves($this->board, $this->getHasMoved(), $forAccountID);
		foreach($moves as $move)
		{
			if($move[0]==$toX && $move[1]==$toY)
			{
				$currentPlayer =& $this->getCurrentTurnPlayer();
				$pieceTaken = null;
				$pieceTakenID = null;
				if($this->board[$toY][$toX] != null)
				{
					$pieceTaken = $this->board[$toY][$toX];
					if($pieceTaken->pieceID == ChessPiece::KING)
					{
						throw new Exception('King was taken.');
					}
				}

				$this->board[$toY][$toX] = $this->board[$y][$x];
				$this->board[$y][$x] = null; 
				$p->setX($toX);
				$p->setY($toY);
				
				//Castling
				$castling = null;
				if($p->pieceID == ChessPiece::KING) {
					$movement = $toX - $x;
					if(abs($movement) == 2) {
						$this->hasMoved[$p->colour][ChessPiece::KING] = true;
						//To the left.
						if($movement == -2) {
							$castling = 'Queen';
							$castleX = 0;
							$castleToX = 3;
						} //To the right
						else if($movement == 2) {
							$castling = 'King';
							$castleX = 7;
							$castleToX = 5;
						}
						$this->board[$toY][$castleToX] = $this->board[$y][$castleX];
						$this->board[$toY][$castleToX]->setX($castleX);
					}
				}
				
				$checking = null;
				if($p->isAttacking($this->board, $this->getHasMoved(), true))
				{
					$checking = 'CHECK';
				}
				if($this->isCheckmated($p->colour == self::PLAYER_WHITE ? self::PLAYER_BLACK : self::PLAYER_BLACK))
				{
					$checking = 'MATE';
				}
				if($this->moves!=null)
				{
					$this->moves[] = $this->createMove($p->pieceID, $x, $y, $toX, $toY, $pieceTakenID, $checking, $this->getCurrentTurnColour(), $castling);
				}
				if($p->isUnderAttack($this->board))
				{
					return 3;
				}
				$otherPlayer =& $this->getCurrentTurnPlayer();
				$this->db->query('INSERT INTO chess_game_moves
								(chess_game_id,piece_id,start_x,start_y,end_x,end_y,checked,piece_taken,castling)
								VALUES
								(' . $p->gameID . ',' . $p->pieceID . ',' . $x . ',' . $y . ',' . $toX . ',' . $toY . ',' . $this->db->escapeString($checking, true, true) . ',' . ($pieceTaken == null ? 'NULL' : $pieceTaken->pieceID) . ',' . $this->db->escapeString($castling, true, true) . ');');
				
				
				$currentPlayer->increaseHOF(1, array('Chess','Moves','Total Taken'), HOF_PUBLIC);
				if($pieceTaken != null)
				{
					$this->db->query('DELETE FROM chess_game_pieces
									WHERE chess_game_id=' . $this->chessGameID . ' AND account_id=' . $pieceTaken->accountID . ' AND piece_id=' . $pieceTaken->pieceID . ' AND piece_no=' . $pieceTaken->pieceNo . ';');
				
					$pieceTakenSymbol = ChessPiece::getSymbolForPiece($pieceTaken, $this->getCurrentTurnColour());
					$currentPlayer->increaseHOF(1, array('Chess','Moves','Opponent Pieces Taken','Total'), HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, array('Chess','Moves','Own Pieces Taken','Total'), HOF_PUBLIC);
					$currentPlayer->increaseHOF(1, array('Chess','Moves','Opponent Pieces Taken',$pieceTakenSymbol), HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, array('Chess','Moves','Own Pieces Taken',$pieceTakenSymbol), HOF_PUBLIC);
				}
				$this->db->query('UPDATE chess_game_pieces
							SET x=' . $toX . ', y=' . $toY . '
							WHERE chess_game_id=' . $this->chessGameID . ' AND account_id=' . $p->accountID . ' AND piece_id=' . $p->pieceID . ' AND piece_no=' . $p->pieceNo . ';');
				if($castling != null) {
					$this->db->query('UPDATE chess_game_pieces
								SET x=' . $castleToX . '
								WHERE chess_game_id=' . $this->chessGameID . ' AND account_id=' . $p->accountID . ' AND x = ' . $castleX . ' AND y = ' . $y . ';');
				}
				$return = 0;
				if($checking != null)
				{
					if($checking == 'MATE')
					{
						$this->db->query('UPDATE chess_game
										SET end_time=' . TIME . ', winner_id=' . $forAccountID . '
										WHERE chess_game_id=' . $this->chessGameID . ';');
						$currentPlayer->increaseHOF(1, array('Chess','Games','Won'), HOF_PUBLIC);
						$otherPlayer->increaseHOF(1, array('Chess','Games','Lost'), HOF_PUBLIC);
						$return = 1;
					}
					else if($checking == 'CHECK')
					{
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
		return SmrPlayer::getPlayer($this->whiteID, $this->getGameID());
	}

	public function getWhiteID() {
		return $this->whiteID;
	}

	public function &getBlackPlayer() {
		return SmrPlayer::getPlayer($this->blackID, $this->getGameID());
	}

	public function getBlackID() {
		return $this->blackID;
	}
	
	public function getColourForAccountID($accountID) {
		return $accountID == $this->getWhiteID() ? self::PLAYER_WHITE : $accountID == $this->getBlackID() ? self::PLAYER_BLACK : false;
	}

	public function getEndDate()
	{
		return $this->endDate;
	}

	public function getWinner()
	{
		return $this->winner;
	}

	public function getHasMoved()
	{
		return $this->hasMoved;
	}

	public function getCurrentTurnColour()
	{
		return count($this->getMoves()) % 2 == 0 ? self::PLAYER_WHITE : self::PLAYER_BLACK;
	}

	public function getCurrentTurnAccountID()
	{
		return count($this->getMoves()) % 2 == 0 ? $this->whiteID : $this->blackID;
	}

	public function &getCurrentTurnPlayer()
	{
		return SmrPlayer::getPlayer($this->getCurrentTurnAccountID(), $this->getGameID());
	}

	public function isCurrentTurn($accountID) {
		return $accountID == $this->getCurrentTurnAccountID();
	}
	
	public function getPlayGameHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','chess_play.php',array('ChessGameID' => $this->chessGameID)));
	}
}
?>