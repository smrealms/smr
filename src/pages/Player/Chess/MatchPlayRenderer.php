<?php declare(strict_types=1);

use Smr\Chess\Loc;

/**
 * @var Smr\Account $ThisAccount
 * @var Smr\Chess\ChessGame $ChessGame
 * @var Smr\Player $ThisPlayer
 * @var Smr\Template $this
 * @var array<int, array<int, ?Smr\Chess\ChessPiece>> $Board
 * @var array<string> $FileCoords
 * @var string $MoveMessage
 * @var string $ChessMoveHREF
 * @var bool $Ended
 * @var ?string $Winner
 */

?>
<p><span id="chess_status">
	<?php if ($Ended) { ?>
		The game has ended.<?php
		if (isset($Winner)) { ?>
			<?php echo $Winner; ?> has won!<?php
		}
	} else { ?>
		It is currently <?php echo $ChessGame->getCurrentTurnPlayer()->getLinkedDisplayName(false); ?>'s turn.<?php
	} ?>
</span></p>
<table>
	<tr>
		<td>
			<div style="height: 484px; width: 500px;">
				<table class="chess chessFont"><?php
					foreach ($Board as $Y => $Row) { ?>
						<tr>
							<td class="chessOutline"><?php echo $Y + 1; ?></td><?php
							foreach ($Row as $X => $Piece) { ?>
								<td id="c<?php echo $X . $Y; ?>" data-x="<?php echo $X; ?>" data-y="<?php echo $Y; ?>" class="ajax<?php if (($X + $Y) % 2 !== 0) { ?> whiteSquare<?php } else { ?> blackSquare<?php } ?>" onClick="highlightMoves.call(this)">
									<div<?php if ($ChessGame->isLastMoveSquare(Loc::validate($X, $Y))) { ?> class="lastMove"<?php } ?>><?php
										if ($Piece !== null) { ?><span class="pointer lastMove"><?php echo $Piece->getPieceSymbol(); ?></span><?php } ?>
									</div>
								</td><?php
							} ?>
						</tr><?php
					}?>
					<tr>
						<td class="chessOutline">&nbsp;</td><?php
						foreach ($FileCoords as $FileCoord) { ?>
							<td class="chessOutline"><?php echo $FileCoord; ?></td><?php
						} ?>
					</tr>
				</table>
			</div>
		</td>
		<td>
			<div class="chat" style="height: 484px; width: 160px; overflow-y:scroll;">
				<table id="moveTable" class="ajax chessFont">
					<?php $this->includeTemplate('includes/ChessMoves.inc.php'); ?>
				</table>
			</div>
		</td>
	</tr>
	<tr>
		<td id="chessMsg" class="ajax"><p><?php echo $MoveMessage; ?></p></td>
		<td id="chessButtons" class="ajax"><?php
			if (!$ChessGame->hasEnded() && $ChessGame->isPlayer($ThisPlayer->getAccountID())) {
				?><div class="buttonA"><a class="buttonA" href="<?php echo $ChessGame->getResignHREF(); ?>"><?php if (count($ChessGame->getMoves()) < 2) { ?>Cancel Game<?php } else { ?>Resign<?php } ?></a></div><?php
			} ?>
		</td>
	</tr>
</table>

<script><?php
	$AvailableMoves = array_pad([], count($Board), []);
	if ($ChessGame->isCurrentTurn($ThisAccount->getAccountID())) {
		$Colour = $ChessGame->getColourForAccountID($ThisAccount->getAccountID());
		foreach ($ChessGame->getBoard()->getPieces($Colour) as $Piece) {
			$Moves = [];
			foreach ($Piece->getPossibleMoves($ChessGame->getBoard()) as $Move) {
				$Moves[] = '#c' . $Move->x . $Move->y;
			}
			$AvailableMoves[$Piece->loc->y][$Piece->loc->x] = implode(',', $Moves);
		}
	} ?>
	var submitMoveHREF = <?php echo $this->addJavascriptForAjax('submitMoveHREF', $ChessMoveHREF); ?>;
	var availableMoves = <?php echo $this->addJavascriptForAjax('availableMoves', $AvailableMoves); ?>;
</script>

<?php
$this->addJavascriptSource('/js/chess_play.js');
