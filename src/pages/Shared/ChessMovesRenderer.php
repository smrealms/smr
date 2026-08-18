<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Chess\ChessGame;

class ChessMovesRenderer {

	public static function render(ChessGame $ChessGame): void {
		$Moves = $ChessGame->getMoves();
		foreach ($Moves as $MoveNumber => $Move) { ?>
			<tr>
				<td><?php echo $MoveNumber + 1; ?>.</td>
				<td><?php echo $Move; ?></td>
			</tr><?php
		}

	}

}
