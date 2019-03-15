<table><?php
	foreach($Team as $OrderID => $Dummy) { ?>
	<tr class="top">
		<td>
			<span class="underline"><?php echo $MemberDescription; ?> <?php echo $OrderID; ?></span><br /><br />
		</td>
		<td>
			<select name="<?php echo $MemberInputName; ?>[]">
				<option value="none">None</option><?php
				foreach($DummyNames as $DummyName) { ?>
					<option value="<?php echo htmlspecialchars($DummyName); ?>"<?php if($Dummy && $DummyName==$Dummy->getPlayerName()){ ?> selected="selected"<?php } ?>><?php echo $DummyName; ?></option><?php
				} ?>
			</select><br />
		</td>
		<td>
			<span class="underline">Current Details</span><?php
				if($Dummy) {
					$Ship = $Dummy->getShip();
					$ShipWeapons = $Ship->getWeapons(); ?>
					<br />Level: <?php echo $Dummy->getLevelID(); ?><br />
					Ship: <?php echo $Ship->getName(); ?> (<?php echo $Ship->getAttackRating(); ?>/<?php echo $Ship->getDefenseRating(); ?>)<br />
					DCS: <?php if($Ship->hasDCS()){ ?>Yes<?php }else{ ?>No<?php } ?><br />
					Weapons: <?php foreach($ShipWeapons as $ShipWeapon){ ?>* <?php echo $ShipWeapon->getName(); ?><br /><?php }
				}
				else{ ?>No Dummy<?php } ?>
		</td>
	</tr><?php
	} ?>
</table>
