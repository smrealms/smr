<?php declare(strict_types=1);

use Smr\Race;

?>
<form method="POST" action="<?php echo $JumpGalaxyHREF; ?>">
	Working on Galaxy:
	<select name="gal_on" onchange="this.form.submit()"><?php
		foreach ($Galaxies as $OtherGalaxy) { ?>
			<option value="<?php echo $OtherGalaxy->getGalaxyID(); ?>"<?php if ($OtherGalaxy->equals($Galaxy)) { ?> selected<?php } ?>><?php
				echo $OtherGalaxy->getDisplayName() . ' (' . $OtherGalaxy->getGalaxyID() . ')'; ?>
			</option><?php
		} ?>
	</select>
</form>
<br />

<form name="FORM" method="POST" action="<?php echo $CreateHREF; ?>">
	<table>
		<tr>
			<td class="center">

				<table class="standard">
					<tr>
						<th>Port Level</th>
						<th>Number of Ports</th>
					</tr><?php
					foreach ($TotalPorts as $Level => $Count) { ?>
						<tr>
							<td class="right">Level <?php echo $Level; ?></td>
							<td><input class="center" type="number" value="<?php echo $Count; ?>" size="5" name="port<?php echo $Level; ?>" onInput="levelCalc();" /></td>
						</tr><?php
					} ?>
					<tr>
						<th class="right">Total</th>
						<td><input class="center" type="number" disabled="disabled" size="5" id="totalLevel" value="<?php echo $Total; ?>" /></td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<div class="buttonA">
								<a class="buttonA" onClick="setZero();">Set All Zero</a>
							</div>
						</td>
					</tr>
				</table>
			</td>

			<td class="center">
				<table class="standard">
					<tr>
						<th>Port Race</th>
						<th>% Distribution</th>
					</tr><?php
					foreach (Race::getAllNames() as $raceID => $raceName) { ?>
						<tr>
							<td class="right"><?php echo $raceName; ?></td>
							<td><input class="center" type="number" size="5" name="race<?php echo $raceID; ?>" value="<?php echo $RacePercents[$raceID]; ?>" onInput="raceCalc();" /></td>
						</tr><?php
					} ?>
					<tr>
						<th class="right">Total</th>
						<td><input class="center" type="number" disabled="disabled" size="5" id="totalRace" value="<?php echo $TotalPercent; ?>" /></td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<div class="buttonA">
								<a class="buttonA" onClick="setEqual();">Set All Equal</a>
							</div>
						</td>
					</tr>
				</table>

			</td>
		</tr>

		<tr>
			<td colspan="3" class="center">
				<?php echo create_submit('submit', 'Create Ports'); ?>
				<br /><br />
				<a href="<?php echo $CancelHREF; ?>" class="submitStyle">Cancel</a>
			</td>
		</tr>
	</table>
</form>

<span class="small">Note: When you press "Create Ports" this will rearrange all current ports.<br />
To add new ports without rearranging everything use the Edit Sector feature.</span>
