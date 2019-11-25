<table class="center standard">
	<tr>
		<th>Galaxy</th>
		<th>ID</th>
		<th>Type</th>
		<th>Size</th>
		<th>Max Force Time</th>
		<th>Connectivity</th>
	</tr>
	<tr>
		<td>
			<form method="POST" action="<?php echo $JumpGalaxyHREF; ?>">
				<select name="gal_on" class="InputFields" onchange="this.form.submit()"><?php
					foreach ($Galaxies as $CurrentGalaxy) { ?>
						<option value="<?php echo $CurrentGalaxy->getGalaxyID(); ?>"<?php if ($CurrentGalaxy->equals($Galaxy)) { ?> selected="SELECTED"<?php } ?>><?php
							echo $CurrentGalaxy->getName(); ?>
						</option><?php
					} ?>
				</select>
			</form>
		</td>
		<td><?php echo $Galaxy->getGalaxyID(); ?> / <?php echo count($Galaxies); ?></td>
		<td><?php echo $Galaxy->getGalaxyType(); ?></td>
		<td><?php echo $Galaxy->getWidth(); ?> x <?php echo $Galaxy->getHeight(); ?></td>
		<td><?php echo $Galaxy->getMaxForceTime() / 3600; ?> hours</td>
		<td id="conn" class="ajax"><?php echo $ActualConnectivity; ?>%</td>
	</tr>
</table>
<br />

<table class="center">
	<tr>
		<td class="top">
			<a href="<?php echo $EditGameDetailsHREF; ?>" class="submitStyle">Edit Game Details</a>
			<p><a href="<?php echo $EditGalaxyDetailsHREF; ?>" class="submitStyle">Edit Galaxies</a></p>
			<p><a href="<?php echo $CheckMapHREF; ?>" class="submitStyle">Check Map</a></p>
			<p><a href="<?php echo $SMRFileHREF; ?>" class="submitStyle" target="_blank">Create SMR file</a></p>
		</td>

		<td class="top">
			<table class="center standard">
				<tr><th>Modify Galaxy</th></tr>
				<tr><td><a href="<?php echo $ModifyLocationsHREF; ?>">Locations</a></td></tr>
				<tr><td><a href="<?php echo $ModifyPlanetsHREF; ?>">Planets</a></td></tr>
				<tr><td><a href="<?php echo $ModifyPortsHREF; ?>">Ports</a></td></tr>
				<tr><td><a href="<?php echo $ModifyWarpsHREF; ?>">Warps</a></td></tr>
			</table>
		</td>

		<td class="top">
			<form method="POST" action="<?php echo $SubmitChangesHREF; ?>">
				<input required type="number" name="connect" placeholder="Connectivity %" class="center" style="width:140px" /><br />
				<input type="submit" name="submit" value="Redo Connections">
			</form>
			<br />
			<form method="POST" action="<?php echo $ModifySectorHREF; ?>">
				<input required type="number" min="1" max="<?php echo $LastSector; ?>" name="sector_edit" placeholder="Sector ID" class="center" style="width:140px" /><br />
				<input type="submit" value="Modify Sector" name="submit">
			</form>
			<p><a href="<?php echo $ResetGalaxyHREF; ?>" class="submitStyle">Reset Current Galaxy</a></p>
		</td>
	</tr>
</table>

<?php
if (isset($Message)) { ?>
	<p class="center"><?php echo $Message; ?></p><?php
} ?>

<?php $this->includeTemplate('includes/SectorMap.inc'); ?>
