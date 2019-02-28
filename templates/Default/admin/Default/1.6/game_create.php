
<h1>Edit Existing Games</h1>
<?php
if (count($EditGames) == 0) { ?>
	There are no games for you to edit.<?php
	if (!$CanEditStartedGames) { ?>
		<br />NOTE: You do not have permission to edit games that have already started.<?php
	}
} else { ?>
	<form method="POST" action="<?php echo $EditGameHREF; ?>">
		<table class="standard">
			<tr>
				<td class="right">
					<select name="game_id"><?php
						foreach($EditGames as $EditGame) {
							?><option value="<?php echo $EditGame->getGameID(); ?>"><?php echo $EditGame->getDisplayName(); ?></option><?php
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="center"><input type="submit" value="Edit" name="Edit"></td>
			</tr>
		</table>
	</form><?php
} ?>

<br /><br />

<h1>Create New Game</h1>
<?php $this->includeTemplate('1.6/GameDetails.inc', ['ProcessingHREF' => $CreateGalaxiesHREF]); ?>
