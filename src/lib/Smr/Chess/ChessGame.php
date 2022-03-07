<?php declare(strict_types=1);

namespace Smr\Chess;

use AbstractSmrPlayer;
use Exception;
use Page;
use SmrAccount;
use SmrPlayer;
use Smr;

class ChessGame {
	const GAMETYPE_STANDARD = 'Standard';
	const PLAYER_BLACK = 'Black';
	const PLAYER_WHITE = 'White';
	protected static array $CACHE_CHESS_GAMES = [];

	private Smr\Database $db;

	private int $chessGameID;
	private int $gameID;
	private int $startDate;
	private int $endDate;
	private int $winner;
	private int $whiteID;
	private int $blackID;

	private array $hasMoved;
	private array $board;
	private array $moves;

	private ?array $lastMove = null;

	public static function getNPCMoveGames(bool $forceUpdate = false): array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT chess_game_id
					FROM npc_logins
					JOIN account USING(login)
					JOIN chess_game ON account_id = black_id OR account_id = white_id
					WHERE end_time > ' . Smr\Epoch::time() . ' OR end_time IS NULL;');
		$games = [];
		foreach ($dbResult->records() as $dbRecord) {
			$game = self::getChessGame($dbRecord->getInt('chess_game_id'), $forceUpdate);
			if ($game->getCurrentTurnAccount()->isNPC()) {
				$games[] = $game;
			}
		}
		return $games;
	}

	public static function getOngoingPlayerGames(AbstractSmrPlayer $player): array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT chess_game_id FROM chess_game WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND (black_id = ' . $db->escapeNumber($player->getAccountID()) . ' OR white_id = ' . $db->escapeNumber($player->getAccountID()) . ') AND (end_time > ' . Smr\Epoch::time() . ' OR end_time IS NULL);');
		$games = [];
		foreach ($dbResult->records() as $dbRecord) {
			$games[] = self::getChessGame($dbRecord->getInt('chess_game_id'));
		}
		return $games;
	}

	public static function getAccountGames(int $accountID): array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT chess_game_id FROM chess_game WHERE black_id = ' . $db->escapeNumber($accountID) . ' OR white_id = ' . $db->escapeNumber($accountID) . ';');
		$games = [];
		foreach ($dbResult->records() as $dbRecord) {
			$games[] = self::getChessGame($dbRecord->getInt('chess_game_id'));
		}
		return $games;
	}

	public static function getChessGame(int $chessGameID, bool $forceUpdate = false): self {
		if ($forceUpdate || !isset(self::$CACHE_CHESS_GAMES[$chessGameID])) {
			self::$CACHE_CHESS_GAMES[$chessGameID] = new self($chessGameID);
		}
		return self::$CACHE_CHESS_GAMES[$chessGameID];
	}

	public function __construct(int $chessGameID) {
		$this->db = Smr\Database::getInstance();
		$dbResult = $this->db->read('SELECT *
						FROM chess_game
						WHERE chess_game_id=' . $this->db->escapeNumber($chessGameID) . ' LIMIT 1;');
		if (!$dbResult->hasRecord()) {
			throw new Exception('Chess game not found: ' . $chessGameID);
		}
		$dbRecord = $dbResult->record();
		$this->chessGameID = $chessGameID;
		$this->gameID = $dbRecord->getInt('game_id');
		$this->startDate = $dbRecord->getInt('start_time');
		$this->endDate = $dbRecord->getInt('end_time');
		$this->whiteID = $dbRecord->getInt('white_id');
		$this->blackID = $dbRecord->getInt('black_id');
		$this->winner = $dbRecord->getInt('winner_id');
		$this->resetHasMoved();
	}

	public static function isValidCoord(int $x, int $y, array &$board): bool {
		return $y < count($board) && $y >= 0 && $x < count($board[$y]) && $x >= 0;
	}

	public static function isPlayerChecked(array &$board, array &$hasMoved, string $colour): bool {
		foreach ($board as &$row) {
			foreach ($row as &$p) {
				if ($p != null && $p->colour != $colour && $p->isAttacking($board, $hasMoved, true)) {
					return true;
				}
			}
		}
		return false;
	}

	private function resetHasMoved(): void {
		$this->hasMoved = [
			self::PLAYER_WHITE => [
				ChessPiece::KING => false,
				ChessPiece::ROOK => [
					'Queen' => false,
					'King' => false
				]
			],
			self::PLAYER_BLACK => [
				ChessPiece::KING => false,
				ChessPiece::ROOK => [
					'Queen' => false,
					'King' => false
				]
			],
			ChessPiece::PAWN => [-1, -1]
		];
	}

	public function rerunGame(bool $debugInfo = false): void {
		$db = Smr\Database::getInstance();

		$db->write('UPDATE chess_game
					SET end_time = NULL, winner_id = 0
					WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ';');
		$db->write('DELETE FROM chess_game_pieces WHERE chess_game_id = ' . $this->db->escapeNumber($this->chessGameID) . ';');
		self::insertPieces($this->chessGameID);

		$dbResult = $db->read('SELECT * FROM chess_game_moves WHERE chess_game_id = ' . $this->db->escapeNumber($this->chessGameID) . ' ORDER BY move_id;');
		$db->write('DELETE FROM chess_game_moves WHERE chess_game_id = ' . $this->db->escapeNumber($this->chessGameID) . ';');
		$this->moves = [];
		unset($this->board);
		unset($this->endDate);
		unset($this->winner);
		$this->resetHasMoved();

		try {
			foreach ($dbResult->records() as $dbRecord) {
				$start_x = $dbRecord->getInt('start_x');
				$start_y = $dbRecord->getInt('start_y');
				$end_x = $dbRecord->getInt('end_x');
				$end_y = $dbRecord->getInt('end_y');
				$colour = $dbRecord->getInt('move_id') % 2 == 1 ? self::PLAYER_WHITE : self::PLAYER_BLACK;
				$promotePieceID = $dbRecord->getInt('promote_piece_id');
				if ($debugInfo === true) {
					echo 'x=', $start_x, ', y=', $start_y, ', endX=', $end_x, ', endY=', $end_y, ', colour=', $colour, EOL;
				}
				if (0 != $this->tryMove($start_x, $start_y, $end_x, $end_y, $colour, $promotePieceID)) {
					break;
				}
			}
		} catch (Exception $e) {
			if ($debugInfo === true) {
				echo $e->getMessage() . EOL . $e->getTraceAsString() . EOL;
			}
			// We probably tried an invalid move - move on.
		}
	}

	public function getBoard(): array {
		if (!isset($this->board)) {
			$dbResult = $this->db->read('SELECT * FROM chess_game_pieces WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ';');
			$pieces = [];
			foreach ($dbResult->records() as $dbRecord) {
				$pieces[] = new ChessPiece($dbRecord->getString('colour'), $dbRecord->getInt('piece_id'), $dbRecord->getInt('x'), $dbRecord->getInt('y'), $dbRecord->getInt('piece_no'));
			}
			$this->board = $this->parsePieces($pieces);
		}
		return $this->board;
	}

	/**
	 * Get the board from black's perspective
	 */
	public function getBoardReversed(): array {
		// Need to reverse both the rows and the files to rotate the board
		$board = array_reverse($this->getBoard(), true);
		foreach ($board as $key => $row) {
			$board[$key] = array_reverse($row, true);
		}
		return $board;
	}

	public function getLastMove(): ?array {
		$this->getMoves();
		return $this->lastMove;
	}

	/**
	 * Determines if a board square is part of the last move
	 * (returns true for both the 'To' and 'From' squares).
	 */
	public function isLastMoveSquare(int $x, int $y): bool {
		$lastMove = $this->getLastMove();
		if ($lastMove === null) {
			return false;
		}
		return ($x == $lastMove['From']['X'] && $y == $lastMove['From']['Y']) || ($x == $lastMove['To']['X'] && $y == $lastMove['To']['Y']);
	}

	public function getMoves(): array {
		if (!isset($this->moves)) {
			$dbResult = $this->db->read('SELECT * FROM chess_game_moves WHERE chess_game_id = ' . $this->db->escapeNumber($this->chessGameID) . ' ORDER BY move_id;');
			$this->moves = [];
			$mate = false;
			foreach ($dbResult->records() as $dbRecord) {
				$pieceTakenID = null;
				if ($dbRecord->hasField('piece_taken')) {
					$pieceTakenID = $dbRecord->getInt('piece_taken');
				}
				$promotionPieceID = null;
				if ($dbRecord->hasField('promote_piece_id')) {
					$promotionPieceID = $dbRecord->getInt('promote_piece_id');
				}
				$this->moves[] = $this->createMove($dbRecord->getInt('piece_id'), $dbRecord->getInt('start_x'), $dbRecord->getInt('start_y'), $dbRecord->getInt('end_x'), $dbRecord->getInt('end_y'), $pieceTakenID, $dbRecord->getField('checked'), $dbRecord->getInt('move_id') % 2 == 1 ? self::PLAYER_WHITE : self::PLAYER_BLACK, $dbRecord->getField('castling'), $dbRecord->getBoolean('en_passant'), $promotionPieceID);
				$mate = $dbRecord->getField('checked') == 'MATE';
			}
			if (!$mate && $this->hasEnded()) {
				if ($this->getWinner() != 0) {
					$this->moves[] = ($this->getWinner() == $this->getWhiteID() ? 'Black' : 'White') . ' Resigned.';
				} elseif (count($this->moves) < 2) {
					$this->moves[] = 'Game Cancelled.';
				} else {
					$this->moves[] = 'Game Drawn.';
				}
			}
		}
		return $this->moves;
	}

	public function getFENString(): string {
		$fen = '';
		$board = $this->getBoard();
		$blanks = 0;
		for ($y = 0; $y < 8; $y++) {
			if ($y > 0) {
				$fen .= '/';
			}
			for ($x = 0; $x < 8; $x++) {
				if ($board[$y][$x] === null) {
					$blanks++;
				} else {
					if ($blanks > 0) {
						$fen .= $blanks;
						$blanks = 0;
					}
					$fen .= $board[$y][$x]->getPieceLetter();
				}
			}
			if ($blanks > 0) {
				$fen .= $blanks;
				$blanks = 0;
			}
		}
		$fen .= match($this->getCurrentTurnColour()) {
			self::PLAYER_WHITE => ' w ',
			self::PLAYER_BLACK => ' b ',
		};

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
			$fen .= match($this->hasMoved[ChessPiece::PAWN][1]) {
				3 => '6',
				4 => '3',
			};
		} else {
			$fen .= '-';
		}
		$fen .= ' 0 ' . floor(count($this->moves) / 2);

		return $fen;
	}

	private static function parsePieces(array $pieces): array {
		$board = [];
		$row = [];
		for ($i = 0; $i < 8; $i++) {
			$row[] = null;
		}
		for ($i = 0; $i < 8; $i++) {
			$board[] = $row;
		}
		foreach ($pieces as $piece) {
			if ($board[$piece->y][$piece->x] != null) {
				throw new Exception('Two pieces found in the same tile.');
			}
			$board[$piece->y][$piece->x] = $piece;
		}
		return $board;
	}

	public static function getStandardGame(): array {
		return [
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::ROOK, 0, 0),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::KNIGHT, 1, 0),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::BISHOP, 2, 0),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::QUEEN, 3, 0),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::KING, 4, 0),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::BISHOP, 5, 0),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::KNIGHT, 6, 0),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::ROOK, 7, 0),

				new ChessPiece(self::PLAYER_BLACK, ChessPiece::PAWN, 0, 1),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::PAWN, 1, 1),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::PAWN, 2, 1),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::PAWN, 3, 1),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::PAWN, 4, 1),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::PAWN, 5, 1),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::PAWN, 6, 1),
				new ChessPiece(self::PLAYER_BLACK, ChessPiece::PAWN, 7, 1),

				new ChessPiece(self::PLAYER_WHITE, ChessPiece::PAWN, 0, 6),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::PAWN, 1, 6),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::PAWN, 2, 6),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::PAWN, 3, 6),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::PAWN, 4, 6),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::PAWN, 5, 6),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::PAWN, 6, 6),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::PAWN, 7, 6),

				new ChessPiece(self::PLAYER_WHITE, ChessPiece::ROOK, 0, 7),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::KNIGHT, 1, 7),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::BISHOP, 2, 7),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::QUEEN, 3, 7),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::KING, 4, 7),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::BISHOP, 5, 7),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::KNIGHT, 6, 7),
				new ChessPiece(self::PLAYER_WHITE, ChessPiece::ROOK, 7, 7),
			];
	}

	public static function insertNewGame(int $startDate, ?int $endDate, AbstractSmrPlayer $whitePlayer, AbstractSmrPlayer $blackPlayer): int {
		$db = Smr\Database::getInstance();
		$chessGameID = $db->insert('chess_game', [
			'start_time' => $db->escapeNumber($startDate),
			'end_time' => $endDate === null ? 'NULL' : $db->escapeNumber($endDate),
			'white_id' => $db->escapeNumber($whitePlayer->getAccountID()),
			'black_id' => $db->escapeNumber($blackPlayer->getAccountID()),
			'game_id' => $db->escapeNumber($whitePlayer->getGameID()),
		]);

		self::insertPieces($chessGameID);
		return $chessGameID;
	}

	private static function insertPieces(int $chessGameID): void {
		$db = Smr\Database::getInstance();
		$pieces = self::getStandardGame();
		foreach ($pieces as $p) {
			$db->insert('chess_game_pieces', [
				'chess_game_id' => $db->escapeNumber($chessGameID),
				'colour' => $db->escapeString($p->colour),
				'piece_id' => $db->escapeNumber($p->pieceID),
				'x' => $db->escapeNumber($p->x),
				'y' => $db->escapeNumber($p->y),
			]);
		}
	}

	private function createMove(int $pieceID, int $startX, int $startY, int $endX, int $endY, ?int $pieceTaken, ?string $checking, string $playerColour, ?string $castling, bool $enPassant, ?int $promotionPieceID): string {
		// This move will be set as the most recent move
		$this->lastMove = [
			'From' => ['X' => $startX, 'Y' => $startY],
			'To'   => ['X' => $endX, 'Y' => $endY],
		];

		$otherPlayerColour = self::getOtherColour($playerColour);
		if ($pieceID == ChessPiece::KING) {
			$this->hasMoved[$playerColour][ChessPiece::KING] = true;
		}
		// Check if the piece moving is a rook and mark it as moved to stop castling.
		if ($pieceID == ChessPiece::ROOK && ($startX == 0 || $startX == 7) && ($startY == ($playerColour == self::PLAYER_WHITE ? 7 : 0))) {
			$this->hasMoved[$playerColour][ChessPiece::ROOK][$startX == 0 ? 'Queen' : 'King'] = true;
		}
		// Check if we've taken a rook and marked them as moved, if they've already moved this does nothing, but if they were taken before moving this stops an issue with trying to castle with a non-existent castle.
		if ($pieceTaken == ChessPiece::ROOK && ($endX == 0 || $endX == 7) && $endY == ($otherPlayerColour == self::PLAYER_WHITE ? 7 : 0)) {
			$this->hasMoved[$otherPlayerColour][ChessPiece::ROOK][$endX == 0 ? 'Queen' : 'King'] = true;
		}
		if ($pieceID == ChessPiece::PAWN && ($startY == 1 || $startY == 6) && ($endY == 3 || $endY == 4)) {
			$this->hasMoved[ChessPiece::PAWN] = [$endX, $endY];
		} else {
			$this->hasMoved[ChessPiece::PAWN] = [-1, -1];
		}
		return ($castling == 'Queen' ? '0-0-0' : ($castling == 'King' ? '0-0' : ''))
			. ChessPiece::getSymbolForPiece($pieceID, $playerColour)
			. chr(ord('a') + $startX)
			. (8 - $startY)
			. ' '
			. ($pieceTaken === null ? '' : ChessPiece::getSymbolForPiece($pieceTaken, $otherPlayerColour))
			. chr(ord('a') + $endX)
			. (8 - $endY)
			. ($promotionPieceID === null ? '' : ChessPiece::getSymbolForPiece($promotionPieceID, $playerColour))
			. ' '
			. ($checking === null ? '' : ($checking == 'CHECK' ? '+' : '++'))
			. ($enPassant ? ' e.p.' : '');
	}

	public function isCheckmated(string $colour): bool {
		$king = null;
		foreach ($this->board as $row) {
			foreach ($row as $piece) {
				if ($piece != null && $piece->pieceID == ChessPiece::KING && $piece->colour == $colour) {
					$king = $piece;
					break;
				}
			}
		}
		if ($king === null) {
			throw new Exception('Could not find the king: game id = ' . $this->chessGameID);
		}
		if (!self::isPlayerChecked($this->board, $this->getHasMoved(), $colour)) {
			return false;
		}
		foreach ($this->board as $row) {
			foreach ($row as $piece) {
				if ($piece != null && $piece->colour == $colour) {
					$moves = $piece->getPossibleMoves($this->board, $this->getHasMoved());
					//There are moves we can make, we are clearly not checkmated.
					if (count($moves) > 0) {
						return false;
					}
				}
			}
		}
		return true;
	}

	public static function isCastling(int $x, int $toX): array|false {
		$movement = $toX - $x;
		return match($movement) {
			-2 => ['Type' => 'Queen', 'X' => 0, 'ToX' => 3],
			2 => ['Type' => 'King', 'X' => 7, 'ToX' => 5],
			default => false,
		};
	}

	public static function movePiece(array &$board, array &$hasMoved, int $x, int $y, int $toX, int $toY, int $pawnPromotionPiece = ChessPiece::QUEEN): array {
		if (!self::isValidCoord($x, $y, $board)) {
			throw new Exception('Invalid from coordinates, x=' . $x . ', y=' . $y);
		}
		if (!self::isValidCoord($toX, $toY, $board)) {
			throw new Exception('Invalid to coordinates, x=' . $toX . ', y=' . $toY);
		}
		$pieceTaken = $board[$toY][$toX];
		$board[$toY][$toX] = $board[$y][$x];
		$p = $board[$toY][$toX];
		$board[$y][$x] = null;
		if ($p === null) {
			throw new Exception('Trying to move non-existent piece: ' . var_export($board, true));
		}
		$p->x = $toX;
		$p->y = $toY;

		$oldPawnMovement = $hasMoved[ChessPiece::PAWN];
		$nextPawnMovement = [-1, -1];
		$castling = false;
		$enPassant = false;
		$rookMoved = false;
		$rookTaken = false;
		$pawnPromotion = false;
		if ($p->pieceID == ChessPiece::KING) {
			//Castling?
			$castling = self::isCastling($x, $toX);
			if ($castling !== false) {
				$hasMoved[$p->colour][ChessPiece::KING] = true;
				$hasMoved[$p->colour][ChessPiece::ROOK][$castling['Type']] = true;
				if ($board[$y][$castling['X']] === null) {
					throw new Exception('Cannot castle with non-existent rook.');
				}
				$board[$toY][$castling['ToX']] = $board[$y][$castling['X']];
				$board[$toY][$castling['ToX']]->x = $castling['ToX'];
				$board[$y][$castling['X']] = null;
			}
		} elseif ($p->pieceID == ChessPiece::PAWN) {
			if ($toY == 0 || $toY == 7) {
				$pawnPromotion = $p->promote($pawnPromotionPiece, $board);
			}
			//Double move to track?
			elseif (($y == 1 || $y == 6) && ($toY == 3 || $toY == 4)) {
				$nextPawnMovement = [$toX, $toY];
			}
			//En passant?
			elseif ($hasMoved[ChessPiece::PAWN][0] == $toX &&
					($hasMoved[ChessPiece::PAWN][1] == 3 && $toY == 2 || $hasMoved[ChessPiece::PAWN][1] == 4 && $toY == 5)) {
				$enPassant = true;
				$pieceTaken = $board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]];
				if ($board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] === null) {
					throw new Exception('Cannot en passant a non-existent pawn.');
				}
				$board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] = null;
			}
		} elseif ($p->pieceID == ChessPiece::ROOK && ($x == 0 || $x == 7) && $y == ($p->colour == self::PLAYER_WHITE ? 7 : 0)) {
			//Rook moved?
			if ($hasMoved[$p->colour][ChessPiece::ROOK][$x == 0 ? 'Queen' : 'King'] === false) {
				// We set rook moved in here as it's used for move info.
				$rookMoved = $x == 0 ? 'Queen' : 'King';
				$hasMoved[$p->colour][ChessPiece::ROOK][$rookMoved] = true;
			}
		}
		// Check if we've taken a rook and marked them as moved, if they've already moved this does nothing, but if they were taken before moving this stops an issue with trying to castle with a non-existent castle.
		if ($pieceTaken != null && $pieceTaken->pieceID == ChessPiece::ROOK && ($toX == 0 || $toX == 7) && $toY == ($pieceTaken->colour == self::PLAYER_WHITE ? 7 : 0)) {
			if ($hasMoved[$pieceTaken->colour][ChessPiece::ROOK][$toX == 0 ? 'Queen' : 'King'] === false) {
				$rookTaken = $toX == 0 ? 'Queen' : 'King';
				$hasMoved[$pieceTaken->colour][ChessPiece::ROOK][$rookTaken] = true;
			}
		}

		$hasMoved[ChessPiece::PAWN] = $nextPawnMovement;
		return ['Castling' => $castling,
				'PieceTaken' => $pieceTaken,
				'EnPassant' => $enPassant,
				'RookMoved' => $rookMoved,
				'RookTaken' => $rookTaken,
				'OldPawnMovement' => $oldPawnMovement,
				'PawnPromotion' => $pawnPromotion
			];
	}

	public static function undoMovePiece(array &$board, array &$hasMoved, int $x, int $y, int $toX, int $toY, array $moveInfo): void {
		if (!self::isValidCoord($x, $y, $board)) {
			throw new Exception('Invalid from coordinates, x=' . $x . ', y=' . $y);
		}
		if (!self::isValidCoord($toX, $toY, $board)) {
			throw new Exception('Invalid to coordinates, x=' . $toX . ', y=' . $toY);
		}
		if ($board[$y][$x] != null) {
			throw new Exception('Undoing move onto another piece? x=' . $x . ', y=' . $y);
		}
		$board[$y][$x] = $board[$toY][$toX];
		$p = $board[$y][$x];
		if ($p === null) {
			throw new Exception('Trying to undo move of a non-existent piece: ' . var_export($board, true));
		}
		$board[$toY][$toX] = $moveInfo['PieceTaken'];
		$p->x = $x;
		$p->y = $y;

		$hasMoved[ChessPiece::PAWN] = $moveInfo['OldPawnMovement'];
		//Castling
		if ($p->pieceID == ChessPiece::KING) {
			$castling = self::isCastling($x, $toX);
			if ($castling !== false) {
				$hasMoved[$p->colour][ChessPiece::KING] = false;
				$hasMoved[$p->colour][ChessPiece::ROOK][$castling['Type']] = false;
				if ($board[$toY][$castling['ToX']] === null) {
					throw new Exception('Cannot undo castle with non-existent castle.');
				}
				$board[$y][$castling['X']] = $board[$toY][$castling['ToX']];
				$board[$y][$castling['X']]->x = $castling['X'];
				$board[$toY][$castling['ToX']] = null;
			}
		} elseif ($moveInfo['EnPassant'] === true) {
			$board[$toY][$toX] = null;
			$board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] = $moveInfo['PieceTaken'];
		} elseif ($moveInfo['RookMoved'] !== false) {
			$hasMoved[$p->colour][ChessPiece::ROOK][$moveInfo['RookMoved']] = false;
		}
		if ($moveInfo['RookTaken'] !== false) {
			$hasMoved[$moveInfo['PieceTaken']->colour][ChessPiece::ROOK][$moveInfo['RookTaken']] = false;
		}
	}

	public function tryAlgebraicMove(string $move): int {
		if (strlen($move) != 4 && strlen($move) != 5) {
			throw new Exception('Move of length "' . strlen($move) . '" is not valid, full move: ' . $move);
		}
		$aVal = ord('a');
		$hVal = ord('h');
		if (ord($move[0]) < $aVal || ord($move[2]) < $aVal
				|| ord($move[0]) > $hVal || ord($move[2]) > $hVal
				|| $move[1] < 1 || $move[3] < 1
				|| $move[1] > 8 || $move[3] > 8) {
			throw new Exception('Invalid move: ' . $move);
		}
		$x = ord($move[0]) - $aVal;
		$y = 8 - $move[1];
		$toX = ord($move[2]) - $aVal;
		$toY = 8 - $move[3];
		$pawnPromotionPiece = ChessPiece::QUEEN;
		if (isset($move[4])) {
			$pawnPromotionPiece = ChessPiece::getPieceForLetter($move[4]);
		}
		return $this->tryMove($x, $y, $toX, $toY, $this->getCurrentTurnColour(), $pawnPromotionPiece);
	}

	public function tryMove(int $x, int $y, int $toX, int $toY, string $forColour, int $pawnPromotionPiece): int {
		if ($this->hasEnded()) {
			return 5;
		}
		if ($this->getCurrentTurnColour() != $forColour) {
			return 4;
		}
		$lastTurnPlayer = $this->getCurrentTurnPlayer();
		$this->getBoard();
		$p = $this->board[$y][$x];
		if ($p === null || $p->colour != $forColour) {
			return 2;
		}

		$moves = $p->getPossibleMoves($this->board, $this->getHasMoved(), $forColour);
		foreach ($moves as $move) {
			if ($move[0] == $toX && $move[1] == $toY) {
				$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
				$currentPlayer = $this->getCurrentTurnPlayer();

				$moveInfo = self::movePiece($this->board, $this->getHasMoved(), $x, $y, $toX, $toY, $pawnPromotionPiece);

				//We have taken the move, we should refresh $p
				$p = $this->board[$toY][$toX];

				$pieceTakenID = null;
				if ($moveInfo['PieceTaken'] != null) {
					$pieceTakenID = $moveInfo['PieceTaken']->pieceID;
					if ($moveInfo['PieceTaken']->pieceID == ChessPiece::KING) {
						throw new Exception('King was taken.');
					}
				}

				$pieceID = $p->pieceID;
				$pieceNo = $p->pieceNo;
				$promotionPieceID = null;
				if ($moveInfo['PawnPromotion'] !== false) {
					$p->pieceID = $moveInfo['PawnPromotion']['PieceID'];
					$p->pieceNo = $moveInfo['PawnPromotion']['PieceNo'];
					$promotionPieceID = $p->pieceID;
				}

				$checking = null;
				if ($p->isAttacking($this->board, $this->getHasMoved(), true)) {
					$checking = 'CHECK';
				}
				if ($this->isCheckmated(self::getOtherColour($p->colour))) {
					$checking = 'MATE';
				}

				$castlingType = $moveInfo['Castling'] === false ? null : $moveInfo['Castling']['Type'];

				$this->getMoves(); // make sure $this->moves is initialized
				$this->moves[] = $this->createMove($pieceID, $x, $y, $toX, $toY, $pieceTakenID, $checking, $this->getCurrentTurnColour(), $castlingType, $moveInfo['EnPassant'], $promotionPieceID);
				if (self::isPlayerChecked($this->board, $this->getHasMoved(), $p->colour)) {
					return 3;
				}

				$otherPlayer = $this->getCurrentTurnPlayer();
				if ($moveInfo['PawnPromotion'] !== false) {
					$piecePromotedSymbol = $p->getPieceSymbol();
					$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Own Pawns Promoted', 'Total'], HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Opponent Pawns Promoted', 'Total'], HOF_PUBLIC);
					$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Own Pawns Promoted', $piecePromotedSymbol], HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Opponent Pawns Promoted', $piecePromotedSymbol], HOF_PUBLIC);
				}

				$this->db->insert('chess_game_moves', [
					'chess_game_id' => $this->db->escapeNumber($this->chessGameID),
					'piece_id' => $this->db->escapeNumber($pieceID),
					'start_x' => $this->db->escapeNumber($x),
					'start_y' => $this->db->escapeNumber($y),
					'end_x' => $this->db->escapeNumber($toX),
					'end_y' => $this->db->escapeNumber($toY),
					'checked' => $this->db->escapeString($checking, true),
					'piece_taken' => $moveInfo['PieceTaken'] === null ? 'NULL' : $this->db->escapeNumber($moveInfo['PieceTaken']->pieceID),
					'castling' => $this->db->escapeString($castlingType, true),
					'en_passant' => $this->db->escapeBoolean($moveInfo['EnPassant']),
					'promote_piece_id' => $moveInfo['PawnPromotion'] == false ? 'NULL' : $this->db->escapeNumber($moveInfo['PawnPromotion']['PieceID']),
				]);

				$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Total Taken'], HOF_PUBLIC);
				if ($moveInfo['PieceTaken'] != null) {
					// Get the owner of the taken piece
					$this->db->write('DELETE FROM chess_game_pieces
									WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ' AND colour=' . $this->db->escapeString($moveInfo['PieceTaken']->colour) . ' AND piece_id=' . $this->db->escapeNumber($moveInfo['PieceTaken']->pieceID) . ' AND piece_no=' . $this->db->escapeNumber($moveInfo['PieceTaken']->pieceNo) . ';');

					$pieceTakenSymbol = $moveInfo['PieceTaken']->getPieceSymbol();
					$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Opponent Pieces Taken', 'Total'], HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Own Pieces Taken', 'Total'], HOF_PUBLIC);
					$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Opponent Pieces Taken', $pieceTakenSymbol], HOF_PUBLIC);
					$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Own Pieces Taken', $pieceTakenSymbol], HOF_PUBLIC);
				}

				$this->db->write('UPDATE chess_game_pieces
							SET x=' . $this->db->escapeNumber($toX) . ', y=' . $this->db->escapeNumber($toY) .
								($moveInfo['PawnPromotion'] !== false ? ', piece_id=' . $this->db->escapeNumber($moveInfo['PawnPromotion']['PieceID']) . ', piece_no=' . $this->db->escapeNumber($moveInfo['PawnPromotion']['PieceNo']) : '') . '
							WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ' AND colour=' . $this->db->escapeString($p->colour) . ' AND piece_id=' . $this->db->escapeNumber($pieceID) . ' AND piece_no=' . $this->db->escapeNumber($pieceNo) . ';');
				if ($moveInfo['Castling'] !== false) {
					$this->db->write('UPDATE chess_game_pieces
								SET x=' . $this->db->escapeNumber($moveInfo['Castling']['ToX']) . '
								WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ' AND colour=' . $this->db->escapeString($p->colour) . ' AND x = ' . $this->db->escapeNumber($moveInfo['Castling']['X']) . ' AND y = ' . $this->db->escapeNumber($y) . ';');
				}
				$return = 0;
				if ($checking == 'MATE') {
					$this->setWinner($this->getColourID($forColour));
					$return = 1;
					SmrPlayer::sendMessageFromCasino($lastTurnPlayer->getGameID(), $this->getCurrentTurnAccountID(), 'You have just lost [chess=' . $this->getChessGameID() . '] against [player=' . $lastTurnPlayer->getPlayerID() . '].');
				} else {
					SmrPlayer::sendMessageFromCasino($lastTurnPlayer->getGameID(), $this->getCurrentTurnAccountID(), 'It is now your turn in [chess=' . $this->getChessGameID() . '] against [player=' . $lastTurnPlayer->getPlayerID() . '].');
					if ($checking == 'CHECK') {
						$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Check Given'], HOF_PUBLIC);
						$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Check Received'], HOF_PUBLIC);
					}
				}
				$currentPlayer->saveHOF();
				$otherPlayer->saveHOF();
				return $return;
			}
		}
		// Invalid move was attempted
		return 6;
	}

	public function getChessGameID(): int {
		return $this->chessGameID;
	}

	public function getStartDate(): int {
		return $this->startDate;
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getWhitePlayer(): AbstractSmrPlayer {
		return SmrPlayer::getPlayer($this->whiteID, $this->getGameID());
	}

	public function getWhiteID(): int {
		return $this->whiteID;
	}

	public function getBlackPlayer(): AbstractSmrPlayer {
		return SmrPlayer::getPlayer($this->blackID, $this->getGameID());
	}

	public function getBlackID(): int {
		return $this->blackID;
	}

	public function getColourID(string $colour): int {
		return match($colour) {
			self::PLAYER_WHITE => $this->getWhiteID(),
			self::PLAYER_BLACK => $this->getBlackID(),
		};
	}

	public function getColourPlayer(string $colour): AbstractSmrPlayer {
		return SmrPlayer::getPlayer($this->getColourID($colour), $this->getGameID());
	}

	public function getColourForAccountID(int $accountID): string {
		return match($accountID) {
			$this->getWhiteID() => self::PLAYER_WHITE,
			$this->getBlackID() => self::PLAYER_BLACK,
		};
	}

	/**
	 * Is the given account ID one of the two players of this game?
	 */
	public function isPlayer(int $accountID): bool {
		return $accountID === $this->getWhiteID() || $accountID === $this->getBlackID();
	}

	public function getEndDate(): ?int {
		return $this->endDate;
	}

	public function hasEnded(): bool {
		return $this->endDate != 0 && $this->endDate <= Smr\Epoch::time();
	}

	public function getWinner(): int {
		return $this->winner;
	}

	public function setWinner(int $accountID): array {
		$this->winner = $accountID;
		$this->endDate = Smr\Epoch::time();
		$this->db->write('UPDATE chess_game
						SET end_time=' . $this->db->escapeNumber(Smr\Epoch::time()) . ', winner_id=' . $this->db->escapeNumber($this->winner) . '
						WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ';');
		$winnerColour = $this->getColourForAccountID($accountID);
		$winningPlayer = $this->getColourPlayer($winnerColour);
		$losingPlayer = $this->getColourPlayer(self::getOtherColour($winnerColour));
		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$winningPlayer->increaseHOF(1, [$chessType, 'Games', 'Won'], HOF_PUBLIC);
		$losingPlayer->increaseHOF(1, [$chessType, 'Games', 'Lost'], HOF_PUBLIC);
		return ['Winner' => $winningPlayer, 'Loser' => $losingPlayer];
	}

	public function &getHasMoved(): array {
		return $this->hasMoved;
	}

	public function getCurrentTurnColour(): string {
		return count($this->getMoves()) % 2 == 0 ? self::PLAYER_WHITE : self::PLAYER_BLACK;
	}

	public function getCurrentTurnAccountID(): int {
		return count($this->getMoves()) % 2 == 0 ? $this->whiteID : $this->blackID;
	}

	public function getCurrentTurnPlayer(): AbstractSmrPlayer {
		return SmrPlayer::getPlayer($this->getCurrentTurnAccountID(), $this->getGameID());
	}

	public function getCurrentTurnAccount(): SmrAccount {
		return SmrAccount::getAccount($this->getCurrentTurnAccountID());
	}

	public function getWhiteAccount(): SmrAccount {
		return SmrAccount::getAccount($this->getWhiteID());
	}

	public function getBlackAccount(): SmrAccount {
		return SmrAccount::getAccount($this->getBlackID());
	}

	public function isCurrentTurn(int $accountID): bool {
		return $accountID == $this->getCurrentTurnAccountID();
	}

	public function isNPCGame(): bool {
		return $this->getWhiteAccount()->isNPC() || $this->getBlackAccount()->isNPC();
	}

	public static function getOtherColour(string $colour): string {
		return match($colour) {
			self::PLAYER_WHITE => self::PLAYER_BLACK,
			self::PLAYER_BLACK => self::PLAYER_WHITE,
		};
	}

	public function resign(int $accountID): int {
		if ($this->hasEnded() || !$this->isPlayer($accountID)) {
			throw new Exception('Invalid resign conditions');
		}
		// If only 1 person has moved then just end the game.
		if (count($this->getMoves()) < 2) {
			$this->endDate = Smr\Epoch::time();
			$this->db->write('UPDATE chess_game
							SET end_time=' . $this->db->escapeNumber(Smr\Epoch::time()) . '
							WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ';');
			return 1;
		} else {
			$loserColour = $this->getColourForAccountID($accountID);
			$winnerAccountID = $this->getColourID(self::getOtherColour($loserColour));
			$results = $this->setWinner($winnerAccountID);
			$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
			$results['Loser']->increaseHOF(1, [$chessType, 'Games', 'Resigned'], HOF_PUBLIC);
			SmrPlayer::sendMessageFromCasino($results['Winner']->getGameID(), $results['Winner']->getPlayerID(), '[player=' . $results['Loser']->getPlayerID() . '] just resigned against you in [chess=' . $this->getChessGameID() . '].');
			return 0;
		}
	}

	public function getPlayGameHREF(): string {
		return Page::create('skeleton.php', 'chess_play.php', ['ChessGameID' => $this->chessGameID])->href();
	}

	public function getResignHREF(): string {
		return Page::create('chess_resign_processing.php', '', ['ChessGameID' => $this->chessGameID])->href();
	}
}
