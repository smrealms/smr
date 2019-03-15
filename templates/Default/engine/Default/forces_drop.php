<form method="POST" action="<?php echo $SubmitHREF; ?>">
	<table class="standard">
		<tr>
			<th>Force</th>
			<th>On Ship</th>
			<th>In Sector</th>
			<th>Drop</th>
			<th>Take</th>
		</tr>

		<tr class="center">
			<td>Mines</td>
			<td><?php echo $ThisShip->getMines(); ?></td>
			<td><?php echo $Forces->getMines(); ?></td>
			<td><input type="number" name="drop_mines" min="0" max="50" value="0" class="InputFields center" style="width:100px;"></td>
			<td><input type="number" name="take_mines" min="0" max="50" value="0" class="InputFields center" style="width:100px;"></td>
		</tr>

		<tr class="center">
			<td>Combat Drones</td>
			<td><?php echo $ThisShip->getCDs(); ?></td>
			<td><?php echo $Forces->getCDs(); ?></td>
			<td><input type="number" name="drop_combat_drones" min="0" max="50" value="0" class="InputFields center" style="width:100px;"></td>
			<td><input type="number" name="take_combat_drones" min="0" max="50" value="0" class="InputFields center" style="width:100px;"></td>
		</tr>

		<tr class="center">
			<td>Scout Drones</td>
			<td><?php echo $ThisShip->getSDs(); ?></td>
			<td><?php echo $Forces->getSDs(); ?></td>
			<td><input type="number" name="drop_scout_drones" min="0" max="50" value="0" class="InputFields center" style="width:100px;"></td>
			<td><input type="number" name="take_scout_drones" min="0" max="50" value="0" class="InputFields center" style="width:100px;"></td>
		</tr>

		<tr class="center">
			<td colspan="3">&nbsp;</td>
			<td colspan="2">
				<input type="submit" name="action" value="Drop/Take" />
			</td>
		</tr>

	</table>
</form>
