<?php

class ChessPiece
{
	const KING = 1;
	const QUEEN = 2;
	const ROOK = 3;
	const BISHOP = 4;
	const KNIGHT = 5;
	const PAWN = 6;

	private $log;

	public $gameID;
	public $accountID;
	public $colour;
	public $pieceID;
	public $pieceNo;
	private $x;
	private $y;

	public function staticx()
	{
		$log = Logger.getLogger('Piece.class');
	}
	
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

	public function isChecked($board)
	{
		foreach($board as $row)
		{
			foreach($row as $p)
			{
				if($p != null && $p->colour != $this->colour && $p->isChecking($board))
				{
					return true;
				}
			}
		}
		return false;
	}
	
	public function isChecking($board)
	{
		$moves = $this->getPossibleMoves($board, null);
		foreach($moves as $move)
		{
			$p = $board[$move[1]][$move[0]];
			if($p!=null)
			{
				if($p->pieceID==self::KING && $colour != $p->colour)
				{
					return true;
				}
			}
		}
		return false;
	}

	public function getPossibleMoves($board, $forAccountID = null)
	{
		$moves = array();
		if($forAccountID == null || $this->accountID == $forAccountID)
		{
			if($this->pieceID==self::PAWN)
			{
				$dirY = $this->colour==ChessGame::PLAYER_BLACK ? 1 : -1;
				$moveY = $this->y+$dirY;
				if(self::isValidCoord($this->x, $moveY, $board) && $board[$moveY][$this->x] == null)
				{
					$moves[] = array($this->x,$moveY);
				}
				if($this->y-$dirY == 0 || $this->y-$dirY*2 == count($board)) //Double move first move
				{
					if($board[$moveY][$this->x] == null && $board[$this->y+$dirY*2][$this->x] == null)
					{
						$moves[] = array($this->x,$this->y+$dirY*2);
					}
				}
				$moveX = $this->x+1;
				if(self::isValidCoord($moveX, $moveY, $board))
				{
					if($board[$moveY][$moveX] != null && $board[$moveY][$moveX]->colour!=$this->colour)
					{
						$moves[] = array($moveX,$moveY);
					}
				}
				$moveX = $this->x-1;
				if(self::isValidCoord($moveX, $moveY, $board))
				{
					if($board[$moveY][$moveX] != null && $board[$moveY][$moveX]->colour!=$this->colour)
					{
						$moves[] = array($moveX,$moveY);
					}
				}
			}
			else if($this->pieceID==self::KING)
			{
				//TODO: Check for check
				for($i = -1; $i < 2; $i++)
				{
					for($j = -1; $j < 2; $j++)
					{
						if($i!=0 || $j!=0)
						{
							$this->addMove($this->x+$i, $this->y+$j, $board, $moves);
						}
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
	
	private function addMove($x, $y, array &$board, array &$moves)
	{
		if(self::isValidCoord($x, $y, $board))
		{
			if($board[$y][$x] == null || $board[$y][$x]->colour!=$this->colour)
			{
				$moves[] = array($x,$y);
				return true;
			}
		}
		return false;
	}
	
	private static function isValidCoord($x, $y, $board)
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