<p>It is currently <span id="turn"><?php echo $ChessGame->getCurrentTurnPlayer()->getLinkedDisplayName(false); ?></span>'s turn.</p>
<table>
	<tr>
		<td>
			<div style="height: 500px; width: 500px;">
				<table class="chess"><?php
					$Board = $ChessGame->getBoard();
					foreach($Board as $Y => $Row)
					{ ?>
						<tr><?php
							foreach($Row as $X => $Cell)
							{ ?>
								<td id="x<?php echo $X; ?>y<?php echo $Y; ?>" class="ajax<?php if(($X+$Y) % 2 == 0) { ?> whiteSquare<?php } ?>" onClick="highlightMoves(<?php echo $X; ?>,<?php echo $Y; ?>)"><?php
									if($Cell==null){ ?>&nbsp;<?php } else { echo $Cell->getPieceSymbol(); } ?>
								</td><?php
							}?>
						</tr><?php
					} ?>
				</table>
			</div>
		</td>
		<td>
			<div class="chat" style="height: 500px; width: 300px; overflow-y:scroll;">
				<table id="moveTable">
					<?php $this->includeTemplate('includes/ChessMoves.inc'); ?>
				</table>
			</div>
		</td>
	</tr>
</table>
<script type="text/javascript">
	var submitMoveHREF = '<?php echo $ChessMoveHREF; ?>',
	availableMoves = {<?php
	foreach($Board as $Y => $Row)
	{
		foreach($Row as $X => $Cell)
		{
			if($Cell!=null)
			{ ?>
				"x<?php echo $X; ?>y<?php echo $Y; ?>": [<?php
					if($ChessGame->isCurrentTurn($ThisAccount->getAccountID()))
					{
						foreach($Cell->getPossibleMoves($Board,$ThisAccount->getAccountID()) as $Move)
						{
							?>{"x":<?php echo $Move[0]; ?>, "y":<?php echo $Move[1]; ?>},<?php
						}
					} ?>
				],
				<?php
			}
		}
	} ?>
	};
</script>
<script type="text/javascript" src="js/chess.js"></script>