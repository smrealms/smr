<form method="POST" action="<?php echo $SelectGameHREF; ?>">
	<select id="InputFields" name="selected_game_id" onchange="this.form.submit()"><?php
		foreach ($Games as $Game) { ?>
			<option <?php if ($Game['Selected']) { ?>selected<?php } ?> value="<?php echo $Game['ID']; ?>"><?php echo $Game['Name']; ?></option><?php
		} ?>
	</select>&nbsp;
	<input type="submit" name="action" value="Select" />
</form>

<?php
if (!empty($SelectedGameID)) { ?>
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
		foreach ($Npcs as $accountID => $npc) { ?>
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
							<select name="race_id" class="InputFields"><?php
								foreach (Globals::getRaces() as $raceID => $race) {
									if ($raceID == RACE_NEUTRAL) {
										continue;
									} ?>
									<option value="<?php echo $raceID; ?>"><?php echo $race['Race Name']; ?></option><?php
								} ?>
							</select>
						</td>
						<td><input name="player_alliance" value="<?php echo $npc['default_alliance']; ?>" /></td>
						<td><input type="submit" name="create_npc_player" value="Create" /></td>
					</form><?php
				} else { ?>
					<td><?php echo $npc['player']->getDisplayName(); ?></td>
					<td><?php echo Globals::getRaceName($npc['player']->getRaceID()); ?></td>
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
	<input type="submit" name="add_npc_account" />
</form>
