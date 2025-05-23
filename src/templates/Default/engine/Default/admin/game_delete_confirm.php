<?php declare(strict_types=1);

?>
Are you sure you want to delete the game: <i><?php echo $Game->getDisplayName(); ?></i>?
<br /><br />

<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	<input type="submit" name="action" value="Yes" />
	&nbsp;&nbsp;
	<input type="submit" name="action" value="No" />
</form>
