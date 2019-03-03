<?php
$Moves = $ChessGame->getMoves();
foreach($Moves as $MoveNumber => $Move) { ?>
	<tr>
		<td><?php echo $MoveNumber + 1; ?>.</td>
		<td><?php echo $Move; ?></td>
	</tr><?php
} ?>
