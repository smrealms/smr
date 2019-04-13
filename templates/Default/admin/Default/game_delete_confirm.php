Are you sure you want to delete the game <i><?php echo $Game->getDisplayName(); ?></i>?<br />
<?php
if ($Game->getEndDate() > TIME) { ?>
	<span class="red"><b>WARNING!</b> This game hasn't ended yet!</span><br /><?php
} ?>
<br />

<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	Do you want to save the game to the history DB?<br />
	<input type="radio" name="save" value="Yes" />Yes<br />
	<input type="radio" name="save" value="No" />No<br /><br />
	<input type="submit" name="action" value="Yes" />
	&nbsp;&nbsp;
	<input type="submit" name="action" value="No" />
</form>
