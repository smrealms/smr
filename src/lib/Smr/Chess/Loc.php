<?php declare(strict_types=1);

namespace Smr\Chess;

use Exception;

/**
 * Represents positions on a chess board.
 */
readonly class Loc {

	public const int MAX_X = Board::NX - 1;
	public const int MAX_Y = Board::NY - 1;

	/**
	 * @param int<0, self::MAX_X> $x
	 * @param int<0, self::MAX_Y> $y
	 */
	public function __construct(
		public int $x,
		public int $y,
	) {}

	public function same(self $other): bool {
		return $this->x === $other->x && $this->y === $other->y;
	}

	public function relative(int $dx = 0, int $dy = 0): self {
		return self::validate($this->x + $dx, $this->y + $dy);
	}

	public function relativeOrNull(int $dx = 0, int $dy = 0): ?self {
		return self::validateOrNull($this->x + $dx, $this->y + $dy);
	}

	/**
	 * Convert the x,y position to algebraic notation.
	 */
	public function algebraic(): string {
		return $this->file() . $this->rank();
	}

	public function file(): string {
		return chr(ord('a') + $this->x);
	}

	public function rank(): int {
		return $this->y + 1;
	}

	/**
	 * @phpstan-assert-if-true int<0, self::MAX_X> $x
	 * @phpstan-assert-if-true int<0, self::MAX_Y> $y
	 */
	private static function isValid(int $x, int $y): bool {
		return ($x >= 0 && $x <= self::MAX_X) && ($y >= 0 && $y <= self::MAX_Y);
	}

	public static function validate(int $x, int $y): self {
		if (!self::isValid($x, $y)) {
			throw new Exception('Invalid position: ' . $x . ',' . $y);
		}
		return new self($x, $y);
	}

	public static function validateOrNull(int $x, int $y): ?self {
		if (!self::isValid($x, $y)) {
			return null;
		}
		return new self($x, $y);
	}

	public static function at(string $coord): self {
		if (strlen($coord) < 2) {
			throw new Exception('Invalid coord given: ' . $coord);
		}
		$x = ord(strtolower($coord[0])) - ord('a');
		$y = str2int(substr($coord, 1)) - 1;
		return self::validate($x, $y);
	}

}
