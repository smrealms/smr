<?php
if (!empty($Message)) { ?>
	<?php echo $Message; ?><?php
} ?>

<p>Chat sharing is a way to allow other players to use a query command
in chat to display information about your trader (example: displaying
your current turns). However, sharing will only take place if the player
is in your alliance.</p>

<p>Here you can do the following:</p>
<ul>
	<li>Add or remove sharing your information with others</li>
	<li>Remove information shared by other players</li>
	<li>Decide if you want to share for all games or just the current game</li>
</ul>
<br />

<h2>Players you're sharing with:</h2>
<br />
<table class="standard">
	<tr class="center">
		<th>Player ID</th>
		<th>Player Name</th>
		<th>All Games</th>
		<th>Action</th>
	</tr><?php
	foreach ($ShareTo as $accountId => $share) { ?>
		<tr class="center">
			<td><?php echo $share['Player ID']; ?></td>
			<td><?php echo $share['Player Name']; ?></td>
			<td><?php echo $share['All Games']; ?></td>
			<form method="POST" action="<?php echo $ProcessingHREF; ?>">
				<input type="hidden" name="game_id" value="<?php echo $share['Game ID']; ?>" />
				<td><button type="submit" name="remove_share_to" value="<?php echo $accountId ?>" style="width:65px">Remove</button></td>
			</form>
		</tr><?php
	} ?>
	<tr>
		<form method="POST" action="<?php echo $ProcessingHREF; ?>">
			<td><input class="center" type="number" name="add_player_id" style="width:60px" /></td>
			<td>&nbsp;</td>
			<td class="center"><input type="checkbox" name="all_games"/></td>
			<td><button type="submit" name="add" style="width:65px">Add</button></td>
		<form>
	</tr>
</table>
<br />
<br />

<h2>Players sharing with you:</h2>
<br /><?php
if ($ShareFrom) { ?>
	<table class="standard">
		<tr class="center">
			<th>Player ID</th>
			<th>Player Name</th>
			<th>All Games</th>
			<th>Action</th>
		</tr><?php
		foreach ($ShareFrom as $accountId => $share) { ?>
			<tr class="center">
				<td><?php echo $share['Player ID']; ?></td>
				<td><?php echo $share['Player Name']; ?></td>
				<td><?php echo $share['All Games']; ?></td>
				<form method="POST" action="<?php echo $ProcessingHREF; ?>">
					<input type="hidden" name="game_id" value="<?php echo $share['Game ID']; ?>" />
					<td><button type="submit" name="remove_share_from" value="<?php echo $accountId ?>" style="width:65px">Remove</button></td>
				</form>
			</tr><?php
		} ?>
	</table><?php
} else { ?>
	None<?php
} ?>
