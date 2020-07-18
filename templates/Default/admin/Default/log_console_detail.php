<a href="<?php echo $BackHREF; ?>"><b>&lt; Back</b></a>
<?php
if ($Action == 'Delete') { ?>
	<p>Deletion was completed successfully!</p><?php
} else { ?>
	<table>
		<tr>
			<td class="top" width="50%">
				<p>Display only the following types:</p>
				<form method="POST" action="<?php echo $UpdateHREF; ?>">
					<input type="submit" name="action" value="Update" />
					<br /><br />
					<?php
					foreach ($LogTypes as $id => $type) { ?>
						<input type="checkbox" name="log_type_ids[<?php echo $id; ?>]" <?php echo in_array($id, $LogTypesChecked) ? 'checked' : ''; ?> ><?php echo $type; ?>
						<br /><?php
					} ?>
				</form>
			</td>

			<td class="top" width="50%">
				<p>Change the notes for these users:</p>
				<form method="POST" action="<?php echo $SaveHREF; ?>">
					<input type="submit" name="action" value="Save" />
					<br /><br />
					<textarea spellcheck="true" name="notes" style="width:300px; height:200px;"><?php echo $FlatNotes; ?></textarea>
				</form>
			</td>
		</tr>
	</table>

	<br />
	The following colors will be used:
	<ul><?php
		foreach ($Colors as $color) { ?>
			<li style="color:<?php echo $color['color']; ?>"><?php echo $color['name']; ?></li><?php
		} ?>
	</ul>

	<table class="standard" width="100%">
		<tr>
			<th>Time</th>
			<th>Log Type</th>
			<th>Sector</th>
			<th>Message</th>
		</tr><?php
		foreach ($Logs as $log) { ?>
			<tr style="color:<?php echo $log['color']; ?>">
				<td><?php echo $log['date']; ?></td>
				<td class="center"><?php echo $log['type']; ?></td>
				<td class="center"><?php echo $log['sectorID']; ?></td>
				<td><?php echo $log['message']; ?></td>
			</tr><?php
		} ?>
	</table><?php
} ?>
