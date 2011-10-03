<?php
if(isset($CreateGameMessage))
{
	echo $CreateGameMessage;
} ?>
<table class="standardnobord"><?php
	foreach($ChessGames as $ChessGame)
	{ ?>
		<tr>
			<td><?php
				echo $ChessGame->getWhitePlayer()->getLinkedDisplayName(false); ?> vs <?php echo $ChessGame->getBlackPlayer()->getLinkedDisplayName(false); ?>
			</td>
			<td>
				<div class="buttonA"><a class="buttonA" href="<?php echo $ChessGame->getPlayGameHREF(); ?>">&nbsp;Play&nbsp;</a></div>
			</td>
		</tr><?php
	}
?>
</table>


<form action="<?php echo Globals::getChessCreateHREF(); ?>" method="POST">
	<label for="player_id">Challenge: </label>
	<select id="player_id" name="player_id"><?php
		foreach($PlayerList as $PlayerID => $PlayerName)
		{
			?><option value="<?php echo $PlayerID; ?>"><?php echo $PlayerName; ?></option><?php
		} ?>
	</select><br/>
	<input type="submit"/>
</form>