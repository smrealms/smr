<div class="center">
	<form method="POST" action="<?php echo $ShowHREF; ?>">
			Show News<br />Min:&nbsp;<input type="number" class="Inputfields" value="<?php echo $Min; ?>" name="min" size="5"> - Max:&nbsp;<input type="number" class="Inputfields" value="<?php echo $Max; ?>" name="max" size="5"><br />
		<input type="submit" name="action" value="Show" />
	</form>

	<br />
	<table class="center standard">
		<tr>
			<th>Time</th>
			<th>News</th>
		</tr><?php
		foreach ($Rows as $Row) { ?>
			<tr>
				<td><?php echo $Row['time']; ?></td>
				<td><?php echo $Row['news']; ?></td>
			</tr><?php
		} ?>
	</table>
</div>
