<ROOT>
	<TILE_LIST><?php
		$Board = $ChessGame->getBoard();
		foreach($Board as $Y => $Row)
		{
			foreach($Row as $X => $Cell)
			{ ?>
				<TILE>
					<X><?php echo $X; ?></X>
					<Y><?php echo $Y; ?></Y><?php
					if($Cell!=null) { ?>
						<INNER_HTML><![CDATA[<span class="pointer"><?php echo $Cell->getPieceSymbol(); ?></span>]]></INNER_HTML>
						<POSSIBLE_MOVE_LIST><?php
							if($ChessGame->isCurrentTurn($ThisAccount->getAccountID()))
							{
								$Moves = $Cell->getPossibleMoves($Board, $ThisAccount->getAccountID());
								foreach($Moves as $Move)
								{
									?><POSSIBLE_MOVE><MOVE_X><?php echo $Move[0]; ?></MOVE_X><MOVE_Y><?php echo $Move[1]; ?></MOVE_Y></POSSIBLE_MOVE><?php
								}
							} ?>
						</POSSIBLE_MOVE_LIST><?php
					}
					else
					{ ?>
						<INNER_HTML>&#160;</INNER_HTML><?php
					} ?>
				</TILE><?php
			}
		} ?>
	</TILE_LIST>
	<MOVE_TABLE>
		<![CDATA[<?php $this->includeTemplate('includes/ChessMoves.inc'); ?>]]>
	</MOVE_TABLE>
	<TURN><![CDATA[<?php echo $ChessGame->getCurrentTurnPlayer()->getLinkedDisplayName(false); ?>]]></TURN>
	<?php if(isset($MoveMessage)) { ?><MOVE_MESSAGE><?php echo $MoveMessage ?></MOVE_MESSAGE><?php } ?>
</ROOT>