<table><?php
	foreach($Team as $OrderID => $Dummy)
	{ ?>
	<tr class="top">
		<td>
			<u><?php echo $MemberDescription .' '. $OrderID ?></u><br /><br />
		</td>
		<td>
			<select name="<?php echo $MemberInputName ?>[]">
				<option value="none">None</option><?php
				foreach($DummyNames as $DummyName)
				{ ?>
					<option value="<?php echo $DummyName ?>"<?php if($Dummy && $DummyName==$Dummy->getPlayerName()){ ?> selected="selected"<?php } ?>><?php echo $DummyName ?></option><?php
				} ?>
			</select><br />
		</td>
		<td>
			<u>Current Details</u><?php
				if($Dummy)
				{
					$Ship =& $Dummy->getShip();
					$ShipWeapons =& $Ship->getWeapons() ?>
					<br />Level: <?php echo $Dummy->getLevelID() ?><br />
					Ship: <?php echo $Ship->getName() ?> (<?php echo $Ship->getAttackRating() ?>/<?php echo $Ship->getDefenseRating() ?>)<br />
					DCS: <?php if($Ship->hasDCS()){ ?>Yes<?php }else{ ?>No<?php } ?><br />
					Weapons: <?php foreach($ShipWeapons as $ShipWeapon){ ?>* <?php echo $ShipWeapon->getName() ?><br /><?php } 
				}
				else{ ?>No Dummy<?php } ?>
		</td>
	</tr>
	<?php } ?>
</table>