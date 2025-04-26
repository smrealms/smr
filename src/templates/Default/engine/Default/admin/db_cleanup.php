<?php declare(strict_types=1);

?>
Current database size: <?php echo $DbSizeMB; ?>
<br /><br />

<?php
if (isset($Results)) { ?>
	<h2>Results<?php echo $Preview ? ' (Preview)' : ''; ?></h2>
	<p>Size of data deleted: <?php echo $DiffMB; ?></p>
	<p>Ended games: <?php echo implode(', ', $EndedGames); ?></p>
	<table class="standard">
		<tr>
			<th>Table Name</th>
			<th>Rows<br /><?php echo $Preview ? 'To Delete' : 'Deleted'; ?></th>
		</tr><?php
		foreach ($Results as $table => $rowsDeleted) { ?>
			<tr>
				<td><?php echo $table; ?></td>
				<td class="center"><?php echo $rowsDeleted; ?></td>
			</tr><?php
		} ?>
	</table>
	<p><a href="<?php echo $BackHREF; ?>">&lt;&lt; Back</a></p>
	<?php
} else { ?>
	<span class="red bold">WARNING: </span>Please back up the database before
	performing this operation!

	<p>By proceeding, you will delete all rows for some large tables
	corresponding to games that have already ended.</p>

	<p><a class="submitStyle" href="<?php echo $PreviewHREF; ?>">Preview</a></p>
	<p><a class="submitStyle" href="<?php echo $DeleteHREF; ?>">Delete</a></p>
	<?php
}
