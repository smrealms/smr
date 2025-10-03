<?php declare(strict_types=1);

use Smr\Race;

/**
 * @var int $SelectedGameID
 */

?>
<form method="POST" action="<?php echo $SelectGameHREF; ?>">
	<select name="selected_game_id" onchange="this.form.submit()"><?php
		foreach ($Games as $Game) { ?>
			<option <?php if ($Game['Selected']) { ?>selected<?php } ?> value="<?php echo $Game['ID']; ?>"><?php echo $Game['Name']; ?></option><?php
		} ?>
	</select>&nbsp;
	<?php echo create_submit('action', 'Select'); ?>
</form>

<?php
if ($SelectedGameID !== 0) { ?>
	<br />
	<table class="standard">
		<tr>
			<th>Login</th>
			<th>Active</th>
			<th>Player Name</th>
			<th>Race</th>
			<th>Alliance</th>
			<th>Status</th>
		</tr><?php
		foreach ($Npcs as $npc) { ?>
			<tr>
				<td><?php echo $npc['login']; ?></td>
				<td class="center">
					<form method="POST" action="<?php echo $npc['href']; ?>">
						<input name="active" type="checkbox" <?php if ($npc['active']) { ?>checked<?php } ?> onclick="this.form.submit()" />
						<input type="hidden" name="active-submit" />
					</form>
				</td><?php
				if (!isset($npc['player'])) {
					// The form wrapping only these columns is invalid HTML, but it works for now... ?>
					<form method="POST" action="<?php echo $npc['href']; ?>">
						<td><input required name="player_name" value="<?php echo $npc['default_player_name']; ?>" /></td>
						<td>
							<select name="race_id"><?php
								foreach (Race::getPlayableIDs() as $raceID) { ?>
									<option value="<?php echo $raceID; ?>"><?php echo Race::getName($raceID); ?></option><?php
								} ?>
							</select>
						</td>
						<td><input name="player_alliance" value="<?php echo $npc['default_alliance']; ?>" /></td>
						<td><?php echo create_submit('create_npc_player', 'Create'); ?></td>
					</form><?php
				} else { ?>
					<td><?php echo $npc['player']->getDisplayName(); ?></td>
					<td><?php echo $npc['player']->getRaceName(); ?></td>
					<td><?php echo $npc['player']->getAllianceDisplayName(); ?></td>
					<td class="center"><?php echo $npc['working'] ? 'Working' : 'Idle'; ?></td><?php
				} ?>
			</tr><?php
		} ?>
	</table><?php
} ?>

<br /><br />
<h2>Add New NPC Login</h2>
<form method="POST" action="<?php echo $AddAccountHREF; ?>">
	<input type="hidden" name="npc_login" value="<?php echo $NextLogin; ?>" />
	Login: <?php echo $NextLogin; ?><br />
	Default Player Name: <input required name="default_player_name" /><br />
	Default Alliance: <input required name="default_alliance" /><br />
	<?php echo create_submit_display('Submit'); ?>
</form>
