<?php declare(strict_types=1);

/**
 * @var string $SubmitHREF
 */

?>
What is the title of this edition?<br />
<form method="POST" action="<?php echo $SubmitHREF; ?>">
	<input type="text" name="title" required class="center" style="width:525;"><br /><br />
	<?php echo create_submit('action', 'Make the paper'); ?>
</form>
