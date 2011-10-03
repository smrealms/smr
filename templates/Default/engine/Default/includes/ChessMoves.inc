<?php
$Moves = $ChessGame->getMoves();
foreach($Moves as $MoveNumber => $Move)
{ ?>
	<tr>
		<td><?php echo $MoveNumber; ?>.</td>
		<td><?php echo $Move; ?></td>
	</tr><?php
} ?>