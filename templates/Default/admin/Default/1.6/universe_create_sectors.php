<div class="center"><?php
	if(isset($Message)) {
		echo $Message; ?><br /><br /><?php
	} ?>

	Working on Galaxy <?php echo $Galaxy->getGalaxyID(); ?>/<?php echo count($Galaxies); ?><br />

	<table align="center" class="standard">
		<tr>
			<th>Name</th>
			<th>Type</th>
			<th>Size</th>
			<th>Max Force Time</th>
			<th>Connectivity</th>
		</tr>
		<tr>
			<td><?php echo $Galaxy->getName(); ?></td>
			<td><?php echo $Galaxy->getGalaxyType(); ?></td>
			<td><?php echo $Galaxy->getWidth(); ?> x <?php echo $Galaxy->getHeight(); ?></td>
			<td><?php echo $Galaxy->getMaxForceTime() / 3600; ?> hours</td>
			<td><?php echo $ActualConnectivity; ?>%</td>
		</tr>
	</table>
	<br />

	<form method="POST" action="<?php echo $JumpGalaxyHREF; ?>">
		<select name="jumpgal" onchange="this.form.submit()"><?php
			foreach($Galaxies as &$CurrentGalaxy) { ?>
				<option value="<?php echo $CurrentGalaxy->getGalaxyID(); ?>"<?php if($CurrentGalaxy->equals($Galaxy)) { ?> selected="SELECTED"<?php } ?>><?php
					echo $CurrentGalaxy->getName(); ?>
				</option><?php
			} ?>
		</select>
		<input type="submit" value="Jump To Galaxy">
	</form>
</div><br />
<?php $this->includeTemplate('includes/SectorMap.inc'); ?>
<table class="center">
	<tr>
		<td>
			<form method="POST" action="<?php echo $SubmitChangesHREF; ?>">
				Connection Percent<br />
				<input type="number" name="connect" value="<?php echo $RequestedConnectivity; ?>" size="3"><br />
				<input type="submit" name="submit" value="Redo Connections">
			</form>
		</td>
		<td>
			<a href="<?php echo $ModifyLocationsHREF; ?>" class="submitStyle">Modify Locations</a><br /><br />
			<a href="<?php echo $ModifyPlanetsHREF; ?>" class="submitStyle">Modify Planets</a><br /><br />
			<a href="<?php echo $ModifyPortsHREF; ?>" class="submitStyle">Modify Ports</a><br /><br />
			<a href="<?php echo $ModifyWarpsHREF; ?>" class="submitStyle">Modify Warps</a><br /><br />
			<a href="<?php echo $SMRFileHREF; ?>" class="submitStyle" target="_self">Create SMR file</a><br /><br />
			<br /><?php
			if (isset($PreviousGalaxyHREF)) { ?>
				<a href="<?php echo $PreviousGalaxyHREF; ?>" class="submitStyle">Previous Galaxy</a><br /><br /><?php
			}
			if (isset($NextGalaxyHREF)) { ?>
				<a href="<?php echo $NextGalaxyHREF; ?>" class="submitStyle">Next Galaxy</a><br /><?php
			} ?>
		</td>
		<td>
			<form method="POST" action="<?php echo $SubmitChangesHREF; ?>">
				Sector ID<br />
				<input type="number" size="5" name="sector_edit"><br />
				<input type="submit" value="Modify Sector" name="submit">
			</form>
		</td>
	</tr>
</table>
