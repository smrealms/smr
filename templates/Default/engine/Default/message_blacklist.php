<?php
if (isset($Message)) {
	echo $Message; ?><br /><br /><?php
} ?>

<h2>Blacklisted Players</h2>

<?php
if ($Blacklist) { ?>
	<br />
	<form method="POST" action="<?php echo $BlacklistDeleteHREF; ?>">
		<table class="standard" width="50%">
			<tr>
				<th>Option</th>
				<th>Game&nbsp;ID</th>
				<th>Name</th>
			</tr><?php
			foreach ($Blacklist as $Entry) { ?>
				<tr>
					<td class="center shrink">
						<input type="checkbox" name="entry_ids[]" value="<?php echo $Entry['entry_id']; ?>">
					</td>
					<td class="center shrink"><?php echo $Entry['game_id']; ?></td>
					<td><?php echo htmlentities($Entry['player_name']); ?></td>
				</tr><?php
			} ?>
		</table><br />
		<input type="submit" name="action" value="Remove Selected" />
	</form>
	<br /><?php
} else { ?>
	<p>You are currently accepting all communications.</p><?php
} ?>

<br /><h2>Blacklist Player</h2><br />
<form method="POST" action="<?php echo $BlacklistAddHREF; ?>">
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Name:&nbsp;</td>
			<td class="mb"><input type="text" name="PlayerName" required size="30"></td>
		</tr>
	</table><br />
	<input type="submit" name="action" value="Blacklist" />
</form>
