<?php declare(strict_types=1);

/**
 * @var int $Max
 * @var int $Min
 * @var string $ShowHREF
 * @var array<array{time: string, news: string}> $Rows
 */

?>
<div class="center">
	<form method="POST" action="<?php echo $ShowHREF; ?>">
			Show News<br />Min:&nbsp;<input type="number" class="Inputfields" value="<?php echo $Min; ?>" name="min" size="5"> - Max:&nbsp;<input type="number" class="Inputfields" value="<?php echo $Max; ?>" name="max" size="5"><br />
		<?php echo create_submit('action', 'Show'); ?>
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
