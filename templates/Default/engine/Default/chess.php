<p style="width: 60%">Challenge other traders to a round of <i>Faster Than Knight</i>,
a game of chess played over the super-photonic transponder aboard your ship.</p>

<?php
if (!empty($ChessGames)) { ?>
	<table class="standard ajax" id="GameList">
		<tr>
			<th>Players</th>
			<th>Current Turn</th>
			<th></th>
		</tr><?php
		foreach ($ChessGames as $ChessGame) { ?>
			<tr>
				<td>
					<?php echo $ChessGame->getWhitePlayer()->getLinkedDisplayName(false); ?>
					vs
					<?php echo $ChessGame->getBlackPlayer()->getLinkedDisplayName(false); ?>
				</td>
				<td>
					<?php echo $ChessGame->getCurrentTurnPlayer()->getLinkedDisplayName(false); ?>
				</td>
				<td>
					<div class="buttonA"><a class="buttonA" href="<?php echo $ChessGame->getPlayGameHREF(); ?>">Play</a></div>
				</td>
			</tr><?php
		} ?>
	</table>
	<small>NOTE: Chess matches do not carry over between games.</small>
	<br /><br /><?php
}

if (count($PlayerList) > 0) { ?>
	<form action="<?php echo Globals::getChessCreateHREF(); ?>" method="POST">
		<label for="player_id">Challenge: </label>
		<select id="player_id" name="player_id"><?php
			foreach ($PlayerList as $PlayerID => $PlayerName) {
				?><option value="<?php echo $PlayerID; ?>"><?php echo $PlayerName; ?></option><?php
			} ?>
		</select>&nbsp;<input type="submit"/>
	</form><?php
} else { ?>
	<p>You have challenged every player.</p><?php
}

if (isset($NPCList)) {
	if (count($NPCList) > 0) { ?>
		<form action="<?php echo Globals::getChessCreateHREF(); ?>" method="POST">
			<label for="player_id">Challenge NPC: </label>
			<select id="player_id" name="player_id"><?php
				foreach ($NPCList as $PlayerID => $PlayerName) {
					?><option value="<?php echo $PlayerID; ?>"><?php echo $PlayerName; ?></option><?php
				} ?>
			</select>&nbsp;<input type="submit"/>
		</form><?php
	} else { ?>
		<p>You have challenged every NPC.</p><?php
	}
} ?>
