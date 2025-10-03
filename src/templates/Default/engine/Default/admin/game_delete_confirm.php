<?php declare(strict_types=1);

?>
Are you sure you want to delete the game: <i><?php echo $Game->getDisplayName(); ?></i>?
<br /><br />

<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	<?php echo create_submit('action', 'Yes'); ?>
	&nbsp;&nbsp;
	<?php echo create_submit('action', 'No'); ?>
</form>
