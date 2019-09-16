<form name="FORM" method="POST" action="<?php echo $CreateHREF; ?>">
	Working on Galaxy : <?php echo $Galaxy->getName(); ?> (<?php echo $Galaxy->getGalaxyID(); ?>)<br />
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
							<td><input class="center" type="number" value="<?php echo $Count; ?>" size="5" name="port<?php echo $Level; ?>" onInput="levelCalc(<?php echo SmrPort::MAX_LEVEL; ?>);" /></td>
						</tr><?php
					} ?>
					<tr>
						<th class="right">Total</th>
						<td><input class="center" type="number" disabled="disabled" size="5" name="total" value="<?php echo $Total; ?>" /></td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<div class="buttonA">
								<a class="buttonA" onClick="setZero(<?php echo SmrPort::MAX_LEVEL; ?>);">Set All Zero</a>
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
					foreach (Globals::getRaces() as $raceID => $race) { ?>
						<tr>
							<td class="right"><?php echo $race['Race Name']; ?></td>
							<td><input class="center" type="number" size="5" name="race<?php echo $raceID; ?>" value="<?php echo $RacePercents[$raceID]; ?>" onInput="raceCalc();" /></td>
						</tr><?php
					} ?>
					<tr>
						<th class="right">Total</th>
						<td><input class="center" type="number" disabled="disabled" size="5" name="racedist" value="<?php echo $TotalPercent; ?>" /></td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<div class="buttonA">
								<a class="buttonA" onClick="setEven();">Set All Equal</a>
							</div>
						</td>
					</tr>
				</table>

			</td>
		</tr>

		<tr>
			<td colspan="3" class="center">
				<input type="submit" name="submit" value="Create Ports" />
				<br /><br />
				<input type="submit" name="submit" value="Cancel" />
			</td>
		</tr>
	</table>
</form>

<span class="small">Note: When you press "Create Ports" this will rearrange all current ports.<br />
To add new ports without rearranging everything use the Edit Sector feature.</span>
