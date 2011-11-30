<?php

class ChessPiece
{
	const KING = 1;
	const QUEEN = 2;
	const ROOK = 3;
	const BISHOP = 4;
	const KNIGHT = 5;
	const PAWN = 6;

	public $gameID;
	public $accountID;
	public $colour;
	public $pieceID;
	public $pieceNo;
	private $x;
	private $y;
	
	public function __construct($gameID, $accountID, $colour, $pieceID, $x, $y, $pieceNo = -1)
	{
		$this->gameID = $gameID;
		$this->accountID = $accountID;
		$this->colour = $colour;
		$this->pieceID = $pieceID;
		$this->pieceNo = $pieceNo;
		$this->x = $x;
		$this->y = $y;
	}

	public function getX()
	{
		return $this->x;
	}
	
	public function getY()
	{
		return $this->y;
	}

	public function setX($x) {
		$this->x = $x;
	}

	public function setY($y) {
		$this->y = $y;
	}
	
	public function isSafeMove(array &$board, &$hasMoved, $toX = -1, $toY = -1) {
		$x = $this->x;
		$y = $this->y;
		$moveInfo = ChessGame::movePiece($board, $hasMoved, $x, $y, $toX, $toY);
		$safe = !$this->isChecked($board, $hasMoved, true);
		ChessGame::undoMovePiece($board, $hasMoved, $x, $y, $toX, $toY, $moveInfo['PieceTaken'], $moveInfo['EnPassant']);
		return $safe;
	}

	public function isChecked(array &$board, array &$hasMoved, $king = true, $x = -1, $y = -1)
	{
		foreach($board as &$row)
		{
			foreach($row as &$p)
			{
				if($p != null && $p->colour != $this->colour && $p->isAttacking($board, $hasMoved, $king, $x, $y))
				{
					return true;
				}
			}
		}
		return false;
	}
	
	public function isAttacking(array &$board, array &$hasMoved, $king, $x = -1, $y = -1)
	{
		$moves =& $this->getPossibleMoves($board, $hasMoved, null, true);
		foreach($moves as &$move)
		{
			$p =& $board[$move[1]][$move[0]];
			if(($move[0] == $x && $move[1] == $y) || ($king === true && $p != null && $p->pieceID == self::KING && $this->colour != $p->colour))
			{
				return true;
			}
		}
		return false;
	}

	public function &getPossibleMoves(array &$board, array &$hasMoved, $forAccountID = null, $attackingCheck = false)
	{
		$moves = array();
		if($forAccountID == null || $this->accountID == $forAccountID)
		{
			if($this->pieceID==self::PAWN)
			{
				$dirY = $this->colour==ChessGame::PLAYER_BLACK ? 1 : -1;
				$moveY = $this->y+$dirY;
				if(!$attackingCheck && self::isValidCoord($this->x, $moveY, $board) && $board[$moveY][$this->x] == null && $this->isSafeMove($board, $hasMoved, $this->x, $moveY))
				{
					$moves[] = array($this->x,$moveY);
				}
				$doubleMoveY = $moveY + $dirY;
				if(!$attackingCheck && ($this->y-$dirY == 0 || $this->y-$dirY*2 == count($board))) //Double move first move
				{
					if($board[$moveY][$this->x] == null && $board[$doubleMoveY][$this->x] == null && $this->isSafeMove($board, $hasMoved, $this->x, $doubleMoveY))
					{
						$moves[] = array($this->x,$doubleMoveY);
					}
				}
				for($i=-1;$i<2;$i+=2) {
					$moveX = $this->x+$i;
					if(self::isValidCoord($moveX, $moveY, $board))
					{
						if($attackingCheck || 
							((($hasMoved[ChessPiece::PAWN][0] == $moveX && ($hasMoved[ChessPiece::PAWN][1] == $moveY-1 || $hasMoved[ChessPiece::PAWN][1] == $moveY+1)) || 
							($board[$moveY][$moveX] != null && $board[$moveY][$moveX]->colour!=$this->colour))
							&& $this->isSafeMove($board, $hasMoved, $moveX, $moveY)))
						{
							$moves[] = array($moveX,$moveY);
						}
					}
				}
			}
			else if($this->pieceID==self::KING)
			{
				for($i = -1; $i < 2; $i++)
				{
					for($j = -1; $j < 2; $j++)
					{
						if($i!=0 || $j!=0)
						{
							$this->addMove($this->x+$i, $this->y+$j, $board, $moves, $hasMoved, $attackingCheck);
						}
					}
				}
				if(!$hasMoved[$this->colour][ChessPiece::KING]) {
					if(!$hasMoved[$this->colour][ChessPiece::ROOK]['Queen'] && $board[$this->y][$this->x-1] == null && $board[$this->y][$this->x-3] == null) {
						$this->addMove($this->x-2, $this->y, $board, $moves, $hasMoved, $attackingCheck);
					}
					if(!$hasMoved[$this->colour][ChessPiece::ROOK]['King'] && $board[$this->y][$this->x+1] == null) {
						$this->addMove($this->x+2, $this->y, $board, $moves, $hasMoved, $attackingCheck);
					}
				}
			}
			else if($this->pieceID==self::QUEEN)
			{
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(--$moveX, $moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Left
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(++$moveX, $moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Right
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove($moveX, ++$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Up
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove($moveX, --$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Down
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(--$moveX, --$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Up-Left
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(++$moveX, --$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Up-Right
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(--$moveX, ++$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Down-Left
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(++$moveX, ++$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Up-Left
			}
			else if($this->pieceID==self::ROOK)
			{
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(--$moveX, $moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Left
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(++$moveX, $moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Right
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove($moveX, ++$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Up
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove($moveX, --$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Down
			}
			else if($this->pieceID==self::BISHOP)
			{
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(--$moveX, --$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Up-Left
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(++$moveX, --$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Up-Right
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(--$moveX, ++$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Down-Left
				$moveX = $this->x;
				$moveY = $this->y;
				while($this->addMove(++$moveX, ++$moveY, $board, $moves) && $board[$moveY][$moveX] == null); //Up-Left
			}
			else if($this->pieceID==self::KNIGHT)
			{
				$moveX = $this->x-1;
				$moveY = $this->y-2;
				$this->addMove($moveX, $moveY, $board, $moves);//2up-left
				$moveX += 2;
				$this->addMove($moveX, $moveY, $board, $moves);//2up-right
				$moveY = $this->y+2;
				$this->addMove($moveX, $moveY, $board, $moves);//2down-right
				$moveX -= 2;
				$this->addMove($moveX, $moveY, $board, $moves);//2down-left
				$moveX = $this->x-2;
				$moveY = $this->y-1;
				$this->addMove($moveX, $moveY, $board, $moves);//2left-up
				$moveY += 2;
				$this->addMove($moveX, $moveY, $board, $moves);//2left-down
				$moveX = $this->x+2;
				$this->addMove($moveX, $moveY, $board, $moves);//2right-down
				$moveY -= 2;
				$this->addMove($moveX, $moveY, $board, $moves);//2right-up
			}
		}
		
		return $moves;
	}
	
	private function addMove($toX, $toY, array &$board, array &$moves, array &$hasMoved = null, $attackingCheck = true)
	{
		if(self::isValidCoord($toX, $toY, $board))
		{
			if(($board[$toY][$toX] == null || $board[$toY][$toX]->colour!=$this->colour) && ($attackingCheck == true || $this->isSafeMove($board, $hasMoved, $toX, $toY)))
			{
				$moves[] = array($toX,$toY);
				return true;
			}
		}
		return false;
	}
	
	private static function isValidCoord($x, $y, array &$board)
	{
		return $y < count($board) && $y >= 0 && $x < count($board[$y]) && $x >= 0;
	}
	
	public function getPieceSymbol()
	{
		return self::getSymbolForPiece($this->pieceID,$this->colour);
	}
	
	public static function getSymbolForPiece($pieceID, $colour)
	{
		return "&#" . (9811 + $pieceID + ($colour == ChessGame::PLAYER_WHITE ? 0 : 6)) . ";";
	}
}
?>