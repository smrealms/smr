<?php declare(strict_types=1);

/**
 * @var string $CreateHREF
 */

?>
Please enter the desired password for your new account.<br /><br />
<form method="POST" action="<?php echo $CreateHREF; ?>">
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Password:&nbsp;</td>
			<td><input name="password" required size="30"></td>
		</tr>
	</table>
	<br />
	<?php echo create_submit('action', 'Create Account'); ?>
</form>
