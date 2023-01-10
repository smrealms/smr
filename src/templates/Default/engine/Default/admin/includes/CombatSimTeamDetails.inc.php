<?php declare(strict_types=1);

?>
<table><?php
	foreach ($Team as $OrderID => $Dummy) { ?>
	<tr class="top">
		<td>
			<span class="bold"><?php echo $OrderID; ?>.</span>
		</td>
		<td>
			<select name="<?php echo $MemberInputName; ?>[]">
				<option value="none">None</option><?php
				foreach ($DummyNames as $DummyName) { ?>
					<option value="<?php echo htmlspecialchars($DummyName); ?>"<?php if ($Dummy && $DummyName == $Dummy->getPlayerName()) { ?> selected="selected"<?php } ?>><?php echo $DummyName; ?></option><?php
				} ?>
			</select><br />
		</td>
		<td><?php
			if ($Dummy) { ?>
				<span class="underline">Current Details</span><br /><?php
				$Ship = $Dummy->getShip();
				$ShipWeapons = $Ship->getWeapons(); ?>
				<span <?php if ($Dummy->isDead()) { ?>class="red"<?php } ?>>
					<?php echo $Ship->getName(); ?> (<?php echo $Ship->getAttackRating(); ?>/<?php echo $Ship->getDefenseRating(); ?>)<br />
				</span>
				Level: <?php echo $Dummy->getLevelID(); ?><br />
				DCS: <?php if ($Ship->hasDCS()) { ?>Yes<?php } else { ?>No<?php } ?><br />
				Weapons:<br /><?php foreach ($ShipWeapons as $ShipWeapon) { ?>* <?php echo $ShipWeapon->getName(); ?><br /><?php }
			} ?>
		</td>
	</tr><?php
	} ?>
</table>
