<?php
if (isset($Message)) { ?>
	<p><span class="red"><?php echo $Message; ?></span></p><?php
} ?>
<p>What account would you like to view?</p>
<form method="POST" action="<?php echo $AnonViewHREF; ?>">
	<p>Anon Account ID: <input required type="number" name="anon_account" /></p>
	<p>Game ID: <input required type="number" name="view_game_id" /></p>
	<input type="submit" name="action" value="Continue" />
</form>
