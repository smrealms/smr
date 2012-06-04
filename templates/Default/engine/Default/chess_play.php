<p>It is currently <span id="turn"><?php
	$CurrentTurnPlayer =& $ChessGame->getCurrentTurnPlayer();
	if($CurrentTurnPlayer == null) {
		?>Unknown<?php
	}
	else {
		echo $CurrentTurnPlayer->getLinkedDisplayName(false);
	} ?></span>'s turn.</p>
<table>
	<tr>
		<td>
			<div style="height: 500px; width: 500px;">
				<table class="chess chessFont">
					<td class="chessOutline">&nbsp;</td><?php
					for($X=ord('a');$X<=ord('h');$X++)
					{ ?>
						<td class="chessOutline"><?php echo chr($X); ?></td><?php
					} ?><?php
					$Board = $ChessGame->getBoard();
					//If we are the black player then reverse the board
					if($ChessGame->getBlackID() == $ThisPlayer->getAccountID()) {
						$Board = array_reverse($Board, true);
					}
					foreach($Board as $Y => $Row)
					{ ?>
						<tr>
							<td class="chessOutline"><?php echo 8-$Y; ?></td><?php
							foreach($Row as $X => $Cell)
							{ ?>
								<td id="x<?php echo $X; ?>y<?php echo $Y; ?>" class="ajax<?php if(($X+$Y) % 2 == 0) { ?> whiteSquare<?php } else { ?> blackSquare<?php } ?>" onClick="highlightMoves(<?php echo $X; ?>,<?php echo $Y; ?>)"><?php
									if($Cell==null){ ?>&nbsp;<?php } else { ?><span class="pointer"><?php echo $Cell->getPieceSymbol(); ?></span><?php } ?>
								</td><?php
							} ?>
							<td class="chessOutline"><?php echo 8-$Y; ?></td>
						</tr><?php
					}?>
					<td class="chessOutline">&nbsp;</td><?php
					for($X=ord('a');$X<=ord('h');$X++)
					{ ?>
						<td class="chessOutline"><?php echo chr($X); ?></td><?php
					} ?>
				</table>
			</div>
		</td>
		<td>
			<div class="chat" style="height: 500px; width: 300px; overflow-y:scroll;">
				<table id="moveTable" class="ajax chessFont">
					<?php $this->includeTemplate('includes/ChessMoves.inc'); ?>
				</table>
			</div>
		</td>
	</tr>
</table>
<script type="text/javascript" ><?php
	$AvailableMoves = array();
	foreach($Board as $Y => $Row) {
		foreach($Row as $X => $Cell) {
			if($Cell!=null) { 
				$XY = 'x' . $X . 'y' . $Y;
				$AvailableMoves[$XY] = array();
				if($ChessGame->isCurrentTurn($ThisAccount->getAccountID())) {
					$Moves = $Cell->getPossibleMoves($Board, $ChessGame->getHasMoved(), $ThisAccount->getAccountID());
					foreach($Moves as $Move) {
						$AvailableMoves[$XY][] = array('x' => $Move[0], 'y' => $Move[1]);
					}
				}
			}
		}
	}
	if(isset($MoveMessage)) {
		$this->addJavascriptAlert($MoveMessage);
	} ?>
	var submitMoveHREF = '<?php echo $ChessMoveHREF; ?>',
		availableMoves = <?php echo $this->addJavascriptForAjax('availableMoves', $AvailableMoves); ?>;
</script>