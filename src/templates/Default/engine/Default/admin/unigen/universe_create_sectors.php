<?php declare(strict_types=1);

/**
 * @var Smr\Galaxy $Galaxy
 * @var ?array{name: string, href: string} $NextGalaxy
 * @var ?array{name: string, href: string} $PrevGalaxy
 * @var Smr\Template $this
 * @var string $GameName
 * @var string $BackButtonHREF
 * @var bool $UniGen True if editing, false if view-only
 */

?>

<a href="<?php echo $BackButtonHREF; ?>">&lt;&lt; Exit game: <?php echo $GameName; ?></a><br /><br />

<div style="display: flex; align-items: center; justify-content: center;">
	<div style="width: 75px; margin-right: 15px; text-align: right;"><?php
		if ($PrevGalaxy !== null) { ?>
			<a href="<?php echo $PrevGalaxy['href']; ?>">
				<img src="/images/album/rew.jpg" alt="<?php echo $PrevGalaxy['name']; ?>" border="0">
			</a>
			<br /><?php echo $PrevGalaxy['name'];
		} ?>
	</div>

	<div>
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
						<select name="gal_on" onchange="this.form.submit()"><?php
							foreach ($Galaxies as $CurrentGalaxy) { ?>
								<option value="<?php echo $CurrentGalaxy->getGalaxyID(); ?>"<?php if ($CurrentGalaxy->equals($Galaxy)) { ?> selected="SELECTED"<?php } ?>><?php
									echo $CurrentGalaxy->getDisplayName(); ?>
								</option><?php
							} ?>
						</select>
					</form>
				</td>
				<td><?php echo $Galaxy->getGalaxyID(); ?> / <?php echo count($Galaxies); ?></td>
				<td><?php echo $Galaxy->getGalaxyType(); ?></td>
				<td><?php echo $Galaxy->getWidth(); ?> x <?php echo $Galaxy->getHeight(); ?></td>
				<td><?php echo format_time($Galaxy->getMaxForceTime()); ?></td>
				<td id="conn" class="ajax"><?php echo $ActualConnectivity; ?>%</td>
			</tr>
		</table>
	</div>

	<div style="width: 75px; margin-left: 15px;"><?php
		if ($NextGalaxy !== null) { ?>
			<a href="<?php echo $NextGalaxy['href']; ?>">
				<img src="/images/album/fwd.jpg" alt="<?php echo $NextGalaxy['name']; ?>" border="0">
			</a>
			<br /><?php echo $NextGalaxy['name'];
		} ?>
	</div>
</div>

<br />

<table class="center">
	<tr>
		<td class="top">
			<p><a href="<?php echo $CheckMapHREF; ?>" class="submitStyle">Check Map</a></p>
			<p><a href="<?php echo $SMRFileHREF; ?>" class="submitStyle" target="_blank">Create SMR file</a></p>
		</td>

		<td class="top"><?php
			if ($UniGen) { ?>
				<form id="create_status" method="POST" action="<?php echo $CreateStatusHREF; ?>"></form>
				<table class="center standard">
					<tr><th>Modify Game</th></tr>
					<tr><td><a href="<?php echo $EditGameDetailsHREF; ?>">Game Settings</a></td></tr>
					<tr><td><a href="<?php echo $EditGalaxyDetailsHREF; ?>">Universe Layout</a></td></tr>
					<tr><td><input type="checkbox" <?php if ($AllEdit) { ?>checked<?php } ?> name="all_edit" form="create_status" onchange="this.form.submit()" title="Check this box to let all map editors modify this game" /> Anyone edit?</td></tr>
					<tr><td><input type="checkbox" <?php if ($MapReady) { ?>checked<?php } ?> name="map_ready" form="create_status" onchange="this.form.submit()" title="Check this box if the map is ready to be enabled" /> Map ready?</td></tr>
				</table><?php
			} ?>
		</td>

		<td class="top"><?php
			if ($UniGen) { ?>
				<table class="center standard">
					<tr><th>Modify Galaxy</th></tr>
					<tr><td><a href="<?php echo $ModifyLocationsHREF; ?>">Locations</a></td></tr>
					<tr><td><a href="<?php echo $ModifyPlanetsHREF; ?>">Planets</a></td></tr>
					<tr><td><a href="<?php echo $ModifyPortsHREF; ?>">Ports</a></td></tr>
					<tr><td><a href="<?php echo $ModifyWarpsHREF; ?>">Warps</a></td></tr>
				</table><?php
			} ?>
		</td>

		<td class="top"><?php
			if ($UniGen) { ?>
				<form method="POST" action="<?php echo $ModifySectorHREF; ?>">
					<input required type="number" min="1" max="<?php echo $LastSector; ?>" name="sector_edit" placeholder="Sector ID" class="center" style="width:140px" /><br />
					<?php echo create_submit('submit', 'Modify Sector'); ?>
				</form><?php
			} ?>
			<br />
			<form method="POST" action="<?php echo $RecenterHREF; ?>">
				<input required type="number" min="<?php echo $Galaxy->getStartSector(); ?>" max="<?php echo $Galaxy->getEndSector(); ?>" name="focus_sector_id" placeholder="Sector ID" class="center" style="width:140px" value="<?php echo $FocusSector ?? ''; ?>" /><br />
				<?php echo create_submit('submit', 'Recenter on Sector'); ?>
			</form>
			<a href="<?php echo $RecenterHREF; ?>" class="submitStyle">Default Center</a>
		</td>

		<td class="top"><?php
			if ($UniGen) { ?>
				<span class="red bold">DANGEROUS OPTIONS</span>
				<p><a href="<?php echo $ResetGalaxyHREF; ?>" class="submitStyle">Reset Current Galaxy</a></p>
				<form method="POST" action="<?php echo $SubmitChangesHREF; ?>">
					<input required type="number" name="connect" placeholder="Connectivity %" class="center" style="width:140px" /><br />
					<?php echo create_submit('submit', 'Redo Connections'); ?>
				</form><?php
			} ?>
		</td>
	</tr>
</table>

<?php
if (isset($Message)) { ?>
	<p class="center"><?php echo $Message; ?></p><?php
} ?>

<?php
$this->includeTemplate('includes/SectorMap.inc.php');
$this->addJavascriptSource('/js/uni_gen.js');
