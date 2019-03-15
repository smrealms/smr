<?php
if (!empty($EmptyResult)) { ?>
	<p><span class="bold red">No trader found that matches your search!</span></p><?php
} ?>

<p>
<form method="POST" action="<?php echo $TraderSearchHREF; ?>">
	Player name:<br />
	<input type="text" name="player_name" class="InputFields" style="width:150px">&nbsp;<input type="submit" name="action" value="Search" class="InputFields" />

	<br /><br /><br />

	Player ID:<br />
	<input type="number" name="player_id" class="InputFields" style="width:50px">&nbsp;<input type="submit" name="action" value="Search" class="InputFields" />

</form>
</p>
