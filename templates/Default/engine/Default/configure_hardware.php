<?php
if (!$ThisShip->hasCloak() && !$ThisShip->hasIllusion() && !$ThisShip->hasJump()) {
	?>You have no configurable hardware installed!<?php
} else { ?>
	<?php
	if ($ThisShip->hasCloak()) { ?>
		<b>Cloaking Device:</b>&nbsp;&nbsp;&nbsp;&nbsp;<div class="buttonA"><a class="buttonA" href="<?php echo $ToggleCloakHREF; ?>"><?php if ($ThisShip->isCloaked()) { ?>Disable<?php } else { ?>Enable(<?php echo TURNS_TO_CLOAK; ?>)<?php } ?></a></div>
		<br /><br />
		<?php
	}
	
	if ($ThisShip->hasIllusion()) { ?>
		<form id="SetIllusionForm" method="POST" action="<?php echo $SetIllusionFormHREF; ?>">
			<b>Illusion Generator:</b><br /><br />
			<table class="nobord">
				<tr>
					<td>Ship:</td>
					<td>
						<select name="ship_id" size="1"><?php
							$CurrentShipID = $ThisShip->hasActiveIllusion() ? $ThisShip->getIllusionShipID() : $ThisShip->getShipTypeID();
							foreach ($IllusionShips as $ShipTypeID => $ShipName) {
								?><option value="<?php echo $ShipTypeID; ?>"<?php if ($CurrentShipID == $ShipTypeID) { ?> selected="selected"<?php } ?>><?php echo $ShipName; ?></option><?php
							} ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Attack/Defense</td>
					<td><input type="number" class="center" name="attack" value="<?php if ($ThisShip->hasActiveIllusion()) { echo $ThisShip->getIllusionAttack(); } else { ?>0<?php } ?>" size="4">&nbsp;/&nbsp;<input type="number" class="center" name="defense" value="<?php if ($ThisShip->hasActiveIllusion()) { echo $ThisShip->getIllusionDefense(); } else { ?>0<?php } ?>" size="4"></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" name="action" value="Set Illusion" />&nbsp;&nbsp;&nbsp;&nbsp;<div class="buttonA"><a class="buttonA" href="<?php echo $DisableIllusionHref; ?>">Disable Illusion</a></div></td>
				</tr>
			</table>
		</form><?php
	}
	$this->includeTemplate('includes/JumpDrive.inc');
} ?>
