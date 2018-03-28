<?php
if (isset($CheckboxFormHREF)) { ?>
	<form method="POST" action="<?php echo $CheckboxFormHREF; ?>">
		<table>
			<tr>
				<td><input name="hide_allied_forces" onchange="this.form.submit()" type="checkbox" <?php if ($HideAlliedForces) { ?>checked<?php }; ?> /></td>
				<td>Hide allied forces</td>
			</tr>
			<tr>
				<td><input name="show_seedlist_sectors" onchange="this.form.submit()" type="checkbox" <?php if ($ShowSeedlistSectors) { ?>checked<?php }; ?> /></td>
				<td>Show seedlist sectors</td>
			</tr>
			<input type=hidden name="change_settings" />
		</table>
	</form><?php
}
?>
