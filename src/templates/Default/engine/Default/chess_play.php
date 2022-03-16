<p>It is currently <span id="turn"><?php
	echo $ChessGame->getCurrentTurnPlayer()->getLinkedDisplayName(false);
?></span>'s turn.</p>
<table>
	<tr>
		<td>
			<div style="height: 484px; width: 500px;">
				<table class="chess chessFont"><?php
					foreach ($Board as $Y => $Row) { ?>
						<tr>
							<td class="chessOutline"><?php echo 8 - $Y; ?></td><?php
							foreach ($Row as $X => $Cell) { ?>
								<td id="c<?php echo $X . $Y; ?>" data-x="<?php echo $X; ?>" data-y="<?php echo $Y; ?>" class="ajax<?php if (($X + $Y) % 2 == 0) { ?> whiteSquare<?php } else { ?> blackSquare<?php } ?>" onClick="highlightMoves.call(this)">
									<div<?php if ($ChessGame->isLastMoveSquare($X, $Y)) { ?> class="lastMove"<?php } ?>><?php
										if ($Cell !== null) { ?><span class="pointer lastMove"><?php echo $Cell->getPieceSymbol(); ?></span><?php } ?>
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
		foreach ($Board as $Y => $Row) {
			foreach ($Row as $X => $Cell) {
				$AvailableMoves[$Y][$X] = [];
				if ($Cell != null) {
					$Moves = $Cell->getPossibleMoves($Board, $ChessGame->getHasMoved(), $Colour);
					foreach ($Moves as $Move) {
						$AvailableMoves[$Y][$X][] = '#c' . $Move[0] . $Move[1];
					}
					$AvailableMoves[$Y][$X] = implode(',', $AvailableMoves[$Y][$X]);
				}
			}
		}
	} ?>
	var submitMoveHREF = <?php echo $this->addJavascriptForAjax('submitMoveHREF', $ChessMoveHREF); ?>;
	var availableMoves = <?php echo $this->addJavascriptForAjax('availableMoves', $AvailableMoves); ?>;
</script>

<?php
$this->addJavascriptSource('/js/chess_play.js');
?>
