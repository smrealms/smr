<?php
if (!empty($EmptyResult)) { ?>
	<p><span class="bold red">No trader found that matches your search!</span></p><?php
} ?>

<form method="POST" action="<?php echo $TraderSearchHREF; ?>">
<p>
	Player name:<br />
	<input type="text" name="player_name" style="width:150px">&nbsp;<input type="submit" name="action" value="Search" />

	<br /><br /><br />

	Player ID:<br />
	<input type="number" name="player_id" style="width:50px">&nbsp;<input type="submit" name="action" value="Search" />

</p>
</form>
