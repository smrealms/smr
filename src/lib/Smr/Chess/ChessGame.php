<?php declare(strict_types=1);

namespace Smr\Chess;

use AbstractSmrPlayer;
use Exception;
use Page;
use Smr;
use Smr\Database;
use Smr\Epoch;
use Smr\Exceptions\UserError;
use SmrAccount;
use SmrPlayer;

class ChessGame {

	public const END_RESIGN = 0;
	public const END_CANCEL = 1;

	protected static array $CACHE_CHESS_GAMES = [];

	private Smr\Database $db;

	private readonly int $whiteID;
	private readonly int $blackID;
	private readonly int $gameID;
	private readonly int $startDate;
	private ?int $endDate;
	private int $winner;

	private array $hasMoved;
	private array $board;
	private array $moves;

	private ?array $lastMove = null;

	public static function getNPCMoveGames(bool $forceUpdate = false): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT chess_game_id
					FROM npc_logins
					JOIN account USING(login)
					JOIN chess_game ON account_id = black_id OR account_id = white_id
					WHERE end_time > ' . Epoch::time() . ' OR end_time IS NULL;');
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
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT chess_game_id FROM chess_game WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND (black_id = ' . $db->escapeNumber($player->getAccountID()) . ' OR white_id = ' . $db->escapeNumber($player->getAccountID()) . ') AND (end_time > ' . Epoch::time() . ' OR end_time IS NULL);');
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

	public function __construct(private readonly int $chessGameID) {
		$this->db = Database::getInstance();
		$dbResult = $this->db->read('SELECT *
						FROM chess_game
						WHERE chess_game_id=' . $this->db->escapeNumber($chessGameID));
		if (!$dbResult->hasRecord()) {
			throw new Exception('Chess game not found: ' . $chessGameID);
		}
		$dbRecord = $dbResult->record();
		$this->gameID = $dbRecord->getInt('game_id');
		$this->startDate = $dbRecord->getInt('start_time');
		$this->endDate = $dbRecord->getNullableInt('end_time');
		$this->whiteID = $dbRecord->getInt('white_id');
		$this->blackID = $dbRecord->getInt('black_id');
		$this->winner = $dbRecord->getInt('winner_id');
		$this->resetHasMoved();
	}

	public static function isValidCoord(int $x, int $y, array $board): bool {
		return $y < count($board) && $y >= 0 && $x < count($board[$y]) && $x >= 0;
	}

	public static function isPlayerChecked(array $board, array $hasMoved, Colour $colour): bool {
		foreach ($board as $row) {
			foreach ($row as $p) {
				if ($p != null && $p->colour != $colour && $p->isAttacking($board, $hasMoved, true)) {
					return true;
				}
			}
		}
		return false;
	}

	private function resetHasMoved(): void {
		$this->hasMoved = [
			Colour::White->value => [
				ChessPiece::KING => false,
				ChessPiece::ROOK => [
					'Queen' => false,
					'King' => false,
				],
			],
			Colour::Black->value => [
				ChessPiece::KING => false,
				ChessPiece::ROOK => [
					'Queen' => false,
					'King' => false,
				],
			],
			ChessPiece::PAWN => [-1, -1],
		];
	}

	public function rerunGame(bool $debugInfo = false): void {
		$db = Database::getInstance();

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
				$colour = $dbRecord->getInt('move_id') % 2 == 1 ? Colour::White : Colour::Black;
				$promotePieceID = $dbRecord->getInt('promote_piece_id');
				if ($debugInfo === true) {
					echo 'x=', $start_x, ', y=', $start_y, ', endX=', $end_x, ', endY=', $end_y, ', colour=', $colour->name, EOL;
				}
				if ($this->tryMove($start_x, $start_y, $end_x, $end_y, $colour, $promotePieceID) != 0) {
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
				$pieces[] = new ChessPiece(Colour::from($dbRecord->getString('colour')), $dbRecord->getInt('piece_id'), $dbRecord->getInt('x'), $dbRecord->getInt('y'), $dbRecord->getInt('piece_no'));
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
				$pieceTakenID = $dbRecord->getNullableInt('piece_taken');
				$promotionPieceID = $dbRecord->getNullableInt('promote_piece_id');
				$this->moves[] = $this->createMove(
					$dbRecord->getInt('piece_id'),
					$dbRecord->getInt('start_x'),
					$dbRecord->getInt('start_y'),
					$dbRecord->getInt('end_x'),
					$dbRecord->getInt('end_y'),
					$pieceTakenID,
					$dbRecord->getNullableString('checked'),
					$dbRecord->getInt('move_id') % 2 == 1 ? Colour::White : Colour::Black,
					$dbRecord->getNullableString('castling'),
					$dbRecord->getBoolean('en_passant'),
					$promotionPieceID
				);
				$mate = $dbRecord->getNullableString('checked') == 'MATE';
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
		$fen .= match ($this->getCurrentTurnColour()) {
			Colour::White => ' w ',
			Colour::Black => ' b ',
		};

		// Castling
		$castling = '';
		foreach (Colour::cases() as $colour) {
			if ($this->hasMoved[$colour->value][ChessPiece::KING] !== true) {
				if ($this->hasMoved[$colour->value][ChessPiece::ROOK]['King'] !== true) {
					$castling .= ChessPiece::getLetterForPiece(ChessPiece::KING, $colour);
				}
				if ($this->hasMoved[$colour->value][ChessPiece::ROOK]['Queen'] !== true) {
					$castling .= ChessPiece::getLetterForPiece(ChessPiece::QUEEN, $colour);
				}
			}
		}
		if ($castling == '') {
			$castling = '-';
		}
		$fen .= $castling . ' ';

		// En passant
		[$pawnX, $pawnY] = $this->hasMoved[ChessPiece::PAWN];
		if ($pawnX != -1) {
			$fen .= chr(ord('a') + $pawnX);
			$fen .= match ($pawnY) {
				3 => '6',
				4 => '3',
				default => throw new Exception('Invalid en passant rank: ' . $pawnY),
			};
		} else {
			$fen .= '-';
		}
		$fen .= ' 0 ' . floor(count($this->moves) / 2);

		return $fen;
	}

	private static function parsePieces(array $pieces): array {
		$row = array_fill(0, 8, null);
		$board = array_fill(0, 8, $row);
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
				new ChessPiece(Colour::Black, ChessPiece::ROOK, 0, 0),
				new ChessPiece(Colour::Black, ChessPiece::KNIGHT, 1, 0),
				new ChessPiece(Colour::Black, ChessPiece::BISHOP, 2, 0),
				new ChessPiece(Colour::Black, ChessPiece::QUEEN, 3, 0),
				new ChessPiece(Colour::Black, ChessPiece::KING, 4, 0),
				new ChessPiece(Colour::Black, ChessPiece::BISHOP, 5, 0),
				new ChessPiece(Colour::Black, ChessPiece::KNIGHT, 6, 0),
				new ChessPiece(Colour::Black, ChessPiece::ROOK, 7, 0),

				new ChessPiece(Colour::Black, ChessPiece::PAWN, 0, 1),
				new ChessPiece(Colour::Black, ChessPiece::PAWN, 1, 1),
				new ChessPiece(Colour::Black, ChessPiece::PAWN, 2, 1),
				new ChessPiece(Colour::Black, ChessPiece::PAWN, 3, 1),
				new ChessPiece(Colour::Black, ChessPiece::PAWN, 4, 1),
				new ChessPiece(Colour::Black, ChessPiece::PAWN, 5, 1),
				new ChessPiece(Colour::Black, ChessPiece::PAWN, 6, 1),
				new ChessPiece(Colour::Black, ChessPiece::PAWN, 7, 1),

				new ChessPiece(Colour::White, ChessPiece::PAWN, 0, 6),
				new ChessPiece(Colour::White, ChessPiece::PAWN, 1, 6),
				new ChessPiece(Colour::White, ChessPiece::PAWN, 2, 6),
				new ChessPiece(Colour::White, ChessPiece::PAWN, 3, 6),
				new ChessPiece(Colour::White, ChessPiece::PAWN, 4, 6),
				new ChessPiece(Colour::White, ChessPiece::PAWN, 5, 6),
				new ChessPiece(Colour::White, ChessPiece::PAWN, 6, 6),
				new ChessPiece(Colour::White, ChessPiece::PAWN, 7, 6),

				new ChessPiece(Colour::White, ChessPiece::ROOK, 0, 7),
				new ChessPiece(Colour::White, ChessPiece::KNIGHT, 1, 7),
				new ChessPiece(Colour::White, ChessPiece::BISHOP, 2, 7),
				new ChessPiece(Colour::White, ChessPiece::QUEEN, 3, 7),
				new ChessPiece(Colour::White, ChessPiece::KING, 4, 7),
				new ChessPiece(Colour::White, ChessPiece::BISHOP, 5, 7),
				new ChessPiece(Colour::White, ChessPiece::KNIGHT, 6, 7),
				new ChessPiece(Colour::White, ChessPiece::ROOK, 7, 7),
			];
	}

	public static function insertNewGame(int $startDate, ?int $endDate, AbstractSmrPlayer $whitePlayer, AbstractSmrPlayer $blackPlayer): int {
		$db = Database::getInstance();
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
		$db = Database::getInstance();
		$pieces = self::getStandardGame();
		foreach ($pieces as $p) {
			$db->insert('chess_game_pieces', [
				'chess_game_id' => $db->escapeNumber($chessGameID),
				'colour' => $db->escapeString($p->colour->value),
				'piece_id' => $db->escapeNumber($p->pieceID),
				'x' => $db->escapeNumber($p->x),
				'y' => $db->escapeNumber($p->y),
			]);
		}
	}

	private function createMove(int $pieceID, int $startX, int $startY, int $endX, int $endY, ?int $pieceTaken, ?string $checking, Colour $playerColour, ?string $castling, bool $enPassant, ?int $promotionPieceID): string {
		// This move will be set as the most recent move
		$this->lastMove = [
			'From' => ['X' => $startX, 'Y' => $startY],
			'To' => ['X' => $endX, 'Y' => $endY],
		];

		$otherPlayerColour = $playerColour->opposite();
		if ($pieceID == ChessPiece::KING) {
			$this->hasMoved[$playerColour->value][ChessPiece::KING] = true;
		}
		// Check if the piece moving is a rook and mark it as moved to stop castling.
		if ($pieceID == ChessPiece::ROOK && ($startX == 0 || $startX == 7) && ($startY == ($playerColour == Colour::White ? 7 : 0))) {
			$this->hasMoved[$playerColour->value][ChessPiece::ROOK][$startX == 0 ? 'Queen' : 'King'] = true;
		}
		// Check if we've taken a rook and marked them as moved, if they've already moved this does nothing, but if they were taken before moving this stops an issue with trying to castle with a non-existent castle.
		if ($pieceTaken == ChessPiece::ROOK && ($endX == 0 || $endX == 7) && $endY == ($otherPlayerColour == Colour::White ? 7 : 0)) {
			$this->hasMoved[$otherPlayerColour->value][ChessPiece::ROOK][$endX == 0 ? 'Queen' : 'King'] = true;
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

	public function isCheckmated(Colour $colour): bool {
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
		if (!self::isPlayerChecked($this->board, $this->hasMoved, $colour)) {
			return false;
		}
		foreach ($this->board as $row) {
			foreach ($row as $piece) {
				if ($piece != null && $piece->colour == $colour) {
					$moves = $piece->getPossibleMoves($this->board, $this->hasMoved);
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
		return match ($movement) {
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
				$hasMoved[$p->colour->value][ChessPiece::KING] = true;
				$hasMoved[$p->colour->value][ChessPiece::ROOK][$castling['Type']] = true;
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
			} elseif (($y == 1 || $y == 6) && ($toY == 3 || $toY == 4)) {
				//Double move to track?
				$nextPawnMovement = [$toX, $toY];
			} elseif ($hasMoved[ChessPiece::PAWN][0] == $toX &&
					($hasMoved[ChessPiece::PAWN][1] == 3 && $toY == 2 || $hasMoved[ChessPiece::PAWN][1] == 4 && $toY == 5)) {
				//En passant?
				$enPassant = true;
				$pieceTaken = $board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]];
				if ($board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] === null) {
					throw new Exception('Cannot en passant a non-existent pawn.');
				}
				$board[$hasMoved[ChessPiece::PAWN][1]][$hasMoved[ChessPiece::PAWN][0]] = null;
			}
		} elseif ($p->pieceID == ChessPiece::ROOK && ($x == 0 || $x == 7) && $y == ($p->colour == Colour::White ? 7 : 0)) {
			//Rook moved?
			if ($hasMoved[$p->colour->value][ChessPiece::ROOK][$x == 0 ? 'Queen' : 'King'] === false) {
				// We set rook moved in here as it's used for move info.
				$rookMoved = $x == 0 ? 'Queen' : 'King';
				$hasMoved[$p->colour->value][ChessPiece::ROOK][$rookMoved] = true;
			}
		}
		// Check if we've taken a rook and marked them as moved, if they've already moved this does nothing, but if they were taken before moving this stops an issue with trying to castle with a non-existent castle.
		if ($pieceTaken != null && $pieceTaken->pieceID == ChessPiece::ROOK && ($toX == 0 || $toX == 7) && $toY == ($pieceTaken->colour == Colour::White ? 7 : 0)) {
			if ($hasMoved[$pieceTaken->colour->value][ChessPiece::ROOK][$toX == 0 ? 'Queen' : 'King'] === false) {
				$rookTaken = $toX == 0 ? 'Queen' : 'King';
				$hasMoved[$pieceTaken->colour->value][ChessPiece::ROOK][$rookTaken] = true;
			}
		}

		$hasMoved[ChessPiece::PAWN] = $nextPawnMovement;
		return [
			'Castling' => $castling,
			'PieceTaken' => $pieceTaken,
			'EnPassant' => $enPassant,
			'RookMoved' => $rookMoved,
			'RookTaken' => $rookTaken,
			'OldPawnMovement' => $oldPawnMovement,
			'PawnPromotion' => $pawnPromotion,
		];
	}

	/**
	 * @param string $move Algebraic notation like "b2b4"
	 */
	public function tryAlgebraicMove(string $move): void {
		if (strlen($move) != 4 && strlen($move) != 5) {
			throw new Exception('Move of length "' . strlen($move) . '" is not valid, full move: ' . $move);
		}
		$file = $move[0];
		/** @var numeric-string $rank */
		$rank = $move[1];
		$toFile = $move[2];
		/** @var numeric-string $toRank */
		$toRank = $move[3];

		$aVal = ord('a');
		$x = ord($file) - $aVal;
		$toX = ord($toFile) - $aVal;
		$y = $rank - 1;
		$toY = $toRank - 1;

		$pawnPromotionPiece = ChessPiece::QUEEN;
		if (isset($move[4])) {
			$pawnPromotionPiece = ChessPiece::getPieceForLetter($move[4]);
		}
		$this->tryMove($x, $y, $toX, $toY, $this->getCurrentTurnColour(), $pawnPromotionPiece);
	}

	public function tryMove(int $x, int $y, int $toX, int $toY, Colour $forColour, int $pawnPromotionPiece): string {
		if ($this->hasEnded()) {
			throw new UserError('This game is already over');
		}
		if ($this->getCurrentTurnColour() != $forColour) {
			throw new UserError('It is not your turn to move');
		}
		$lastTurnPlayer = $this->getCurrentTurnPlayer();
		$this->getBoard();
		$p = $this->board[$y][$x];
		if ($p === null || $p->colour != $forColour) {
			throw new UserError('There is no piece on that square');
		}

		$moves = $p->getPossibleMoves($this->board, $this->hasMoved, $forColour);
		$moveIsLegal = false;
		foreach ($moves as $move) {
			if ($move[0] == $toX && $move[1] == $toY) {
				$moveIsLegal = true;
				break;
			}
		}
		if (!$moveIsLegal) {
			throw new UserError('That move is not legal');
		}

		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$currentPlayer = $this->getCurrentTurnPlayer();

		$moveInfo = self::movePiece($this->board, $this->hasMoved, $x, $y, $toX, $toY, $pawnPromotionPiece);

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
		if ($p->isAttacking($this->board, $this->hasMoved, true)) {
			$checking = 'CHECK';
		}
		if ($this->isCheckmated($p->colour->opposite())) {
			$checking = 'MATE';
		}

		$castlingType = $moveInfo['Castling'] === false ? null : $moveInfo['Castling']['Type'];

		$this->getMoves(); // make sure $this->moves is initialized
		$this->moves[] = $this->createMove($pieceID, $x, $y, $toX, $toY, $pieceTakenID, $checking, $this->getCurrentTurnColour(), $castlingType, $moveInfo['EnPassant'], $promotionPieceID);
		if (self::isPlayerChecked($this->board, $this->hasMoved, $p->colour)) {
			throw new UserError('You cannot end your turn in check');
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
							WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ' AND colour=' . $this->db->escapeString($moveInfo['PieceTaken']->colour->value) . ' AND piece_id=' . $this->db->escapeNumber($moveInfo['PieceTaken']->pieceID) . ' AND piece_no=' . $this->db->escapeNumber($moveInfo['PieceTaken']->pieceNo) . ';');

			$pieceTakenSymbol = $moveInfo['PieceTaken']->getPieceSymbol();
			$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Opponent Pieces Taken', 'Total'], HOF_PUBLIC);
			$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Own Pieces Taken', 'Total'], HOF_PUBLIC);
			$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Opponent Pieces Taken', $pieceTakenSymbol], HOF_PUBLIC);
			$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Own Pieces Taken', $pieceTakenSymbol], HOF_PUBLIC);
		}

		$this->db->write('UPDATE chess_game_pieces
					SET x=' . $this->db->escapeNumber($toX) . ', y=' . $this->db->escapeNumber($toY) .
						($moveInfo['PawnPromotion'] !== false ? ', piece_id=' . $this->db->escapeNumber($moveInfo['PawnPromotion']['PieceID']) . ', piece_no=' . $this->db->escapeNumber($moveInfo['PawnPromotion']['PieceNo']) : '') . '
					WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ' AND colour=' . $this->db->escapeString($p->colour->value) . ' AND piece_id=' . $this->db->escapeNumber($pieceID) . ' AND piece_no=' . $this->db->escapeNumber($pieceNo) . ';');
		if ($moveInfo['Castling'] !== false) {
			$this->db->write('UPDATE chess_game_pieces
						SET x=' . $this->db->escapeNumber($moveInfo['Castling']['ToX']) . '
						WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ' AND colour=' . $this->db->escapeString($p->colour->value) . ' AND x = ' . $this->db->escapeNumber($moveInfo['Castling']['X']) . ' AND y = ' . $this->db->escapeNumber($y) . ';');
		}

		if ($checking == 'MATE') {
			$message = 'You have checkmated your opponent, congratulations!';
			$this->setWinner($this->getColourID($forColour));
			SmrPlayer::sendMessageFromCasino($lastTurnPlayer->getGameID(), $this->getCurrentTurnAccountID(), 'You have just lost [chess=' . $this->getChessGameID() . '] against [player=' . $lastTurnPlayer->getPlayerID() . '].');
		} else {
			$message = ''; // non-mating valid move, no message
			SmrPlayer::sendMessageFromCasino($lastTurnPlayer->getGameID(), $this->getCurrentTurnAccountID(), 'It is now your turn in [chess=' . $this->getChessGameID() . '] against [player=' . $lastTurnPlayer->getPlayerID() . '].');
			if ($checking == 'CHECK') {
				$currentPlayer->increaseHOF(1, [$chessType, 'Moves', 'Check Given'], HOF_PUBLIC);
				$otherPlayer->increaseHOF(1, [$chessType, 'Moves', 'Check Received'], HOF_PUBLIC);
			}
		}
		$currentPlayer->saveHOF();
		$otherPlayer->saveHOF();
		return $message;
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

	public function getColourID(Colour $colour): int {
		return match ($colour) {
			Colour::White => $this->getWhiteID(),
			Colour::Black => $this->getBlackID(),
		};
	}

	public function getColourPlayer(Colour $colour): AbstractSmrPlayer {
		return SmrPlayer::getPlayer($this->getColourID($colour), $this->getGameID());
	}

	public function getColourForAccountID(int $accountID): Colour {
		return match ($accountID) {
			$this->getWhiteID() => Colour::White,
			$this->getBlackID() => Colour::Black,
			default => throw new Exception('Account ID is not in this chess game: ' . $accountID),
		};
	}

	/**
	 * Is the given account ID one of the two players of this game?
	 */
	public function isPlayer(int $accountID): bool {
		return $accountID === $this->getWhiteID() || $accountID === $this->getBlackID();
	}

	public function hasEnded(): bool {
		return $this->endDate !== null && $this->endDate <= Epoch::time();
	}

	public function getWinner(): int {
		return $this->winner;
	}

	public function setWinner(int $accountID): array {
		$this->winner = $accountID;
		$this->endDate = Epoch::time();
		$this->db->write('UPDATE chess_game
						SET end_time=' . $this->db->escapeNumber(Epoch::time()) . ', winner_id=' . $this->db->escapeNumber($this->winner) . '
						WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ';');
		$winnerColour = $this->getColourForAccountID($accountID);
		$winningPlayer = $this->getColourPlayer($winnerColour);
		$losingPlayer = $this->getColourPlayer($winnerColour->opposite());
		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$winningPlayer->increaseHOF(1, [$chessType, 'Games', 'Won'], HOF_PUBLIC);
		$losingPlayer->increaseHOF(1, [$chessType, 'Games', 'Lost'], HOF_PUBLIC);
		return ['Winner' => $winningPlayer, 'Loser' => $losingPlayer];
	}

	public function &getHasMoved(): array {
		return $this->hasMoved;
	}

	public function getCurrentTurnColour(): Colour {
		return count($this->getMoves()) % 2 == 0 ? Colour::White : Colour::Black;
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

	/**
	 * @return self::END_*
	 */
	public function resign(int $accountID): int {
		if ($this->hasEnded() || !$this->isPlayer($accountID)) {
			throw new Exception('Invalid resign conditions');
		}

		// If only 1 person has moved then just end the game.
		if (count($this->getMoves()) < 2) {
			$this->endDate = Epoch::time();
			$this->db->write('UPDATE chess_game
							SET end_time=' . $this->db->escapeNumber(Epoch::time()) . '
							WHERE chess_game_id=' . $this->db->escapeNumber($this->chessGameID) . ';');
			return self::END_CANCEL;
		}

		$loserColour = $this->getColourForAccountID($accountID);
		$winnerAccountID = $this->getColourID($loserColour->opposite());
		$results = $this->setWinner($winnerAccountID);
		$chessType = $this->isNPCGame() ? 'Chess (NPC)' : 'Chess';
		$results['Loser']->increaseHOF(1, [$chessType, 'Games', 'Resigned'], HOF_PUBLIC);
		SmrPlayer::sendMessageFromCasino($results['Winner']->getGameID(), $results['Winner']->getAccountID(), '[player=' . $results['Loser']->getPlayerID() . '] just resigned against you in [chess=' . $this->getChessGameID() . '].');
		return self::END_RESIGN;
	}

	public function getPlayGameHREF(): string {
		return Page::create('chess_play.php', ['ChessGameID' => $this->chessGameID])->href();
	}

	public function getResignHREF(): string {
		return Page::create('chess_resign_processing.php', ['ChessGameID' => $this->chessGameID])->href();
	}

}
