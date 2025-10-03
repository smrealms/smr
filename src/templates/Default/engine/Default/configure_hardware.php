<?php declare(strict_types=1);

/**
 * @var Smr\Ship $ThisShip
 * @var Smr\Template $this
 * @var ?string $ToggleCloakHREF
 * @var ?string $SetIllusionFormHREF
 * @var ?string $DisableIllusionHref
 * @var ?array<int, string> $IllusionShips
 */

if (!$ThisShip->hasCloak() && !$ThisShip->hasIllusion() && !$ThisShip->hasJump()) {
	?>You have no configurable hardware installed!<?php
} else { ?>
	<?php
	if ($ThisShip->hasCloak()) { ?>
		<b>Cloaking Device:</b>&nbsp;&nbsp;&nbsp;&nbsp;<div class="buttonA"><a class="buttonA" href="<?php echo $ToggleCloakHREF; ?>"><?php if ($ThisShip->isCloaked()) { ?>Disable<?php } else { ?>Enable(<?php echo TURNS_TO_CLOAK; ?>)<?php } ?></a></div>
		<br /><br />
		<?php
	}

	if (isset($IllusionShips)) { ?>
		<form id="SetIllusionForm" method="POST" action="<?php echo $SetIllusionFormHREF; ?>">
			<b>Illusion Generator:</b><br /><br />
			<table class="nobord">
				<tr>
					<td>Ship:</td>
					<td>
						<select name="ship_type_id" size="1"><?php
							$CurrentShipID = $ThisShip->hasActiveIllusion() ? $ThisShip->getIllusion()->shipTypeID : $ThisShip->getTypeID();
							foreach ($IllusionShips as $ShipTypeID => $ShipName) {
								?><option value="<?php echo $ShipTypeID; ?>"<?php if ($CurrentShipID === $ShipTypeID) { ?> selected="selected"<?php } ?>><?php echo $ShipName; ?></option><?php
							} ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Attack/Defense</td>
					<td><input type="number" class="center" name="attack" value="<?php if ($ThisShip->hasActiveIllusion()) { echo $ThisShip->getIllusion()->attackRating; } else { ?>0<?php } ?>" size="4">&nbsp;/&nbsp;<input type="number" class="center" name="defense" value="<?php if ($ThisShip->hasActiveIllusion()) { echo $ThisShip->getIllusion()->defenseRating; } else { ?>0<?php } ?>" size="4"></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><?php echo create_submit('action', 'Set Illusion'); ?>&nbsp;&nbsp;&nbsp;&nbsp;<div class="buttonA"><a class="buttonA" href="<?php echo $DisableIllusionHref; ?>">Disable Illusion</a></div></td>
				</tr>
			</table>
		</form><?php
	}
	$this->includeTemplate('includes/JumpDrive.inc.php');
}
