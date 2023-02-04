<?php declare(strict_types=1);

use Smr\Race;

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Sector $ThisSector
 * @var string $MapHREF
 * @var string $MoneyHREF
 * @var string $UnoHREF
 * @var string $RemoveWeaponsHREF
 * @var string $AddWeaponHREF
 * @var array<int, string> $WeaponList
 * @var string $ShipHREF
 * @var array<int, string> $ShipList
 * @var string $HardwareHREF
 * @var array<int, string> $Hardware
 * @var string $WarpHREF
 * @var string $TurnsHREF
 * @var string $ExperienceHREF
 * @var string $AlignmentHREF
 * @var string $PersonalRelationsHREF
 * @var string $RaceRelationsHREF
 * @var string $ChangeRaceHREF
 * @var ?string $MaxBuildingsHREF
 * @var ?string $MaxDefensesHREF
 * @var ?string $MaxStockpileHREF
 */

?>
<span class="bold red">WARNING! Please be reasonable with the changes you make! For example, do not load more onto a ship than it is supposed to have, don't put yourself in a sector that doesn't exist, etc.</span><br />

<p><a href="<?php echo $MapHREF; ?>">Map all sectors</a></p>
<p><a href="<?php echo $MoneyHREF; ?>">Load up the $$!</a></p>
<p><a href="<?php echo $UnoHREF; ?>">UNO to full</a></p>
<p><a href="<?php echo $RemoveWeaponsHREF; ?>">Remove all weapons</a></p>

<form method="POST" action="<?php echo $AddWeaponHREF; ?>">
	<input type="number" name="amount" value="1" style="width:75px" />&nbsp;
	<select name="weapon_id"><?php
		foreach ($WeaponList as $weaponTypeID => $weaponName) { ?>
			<option value="<?php echo $weaponTypeID; ?>"><?php echo $weaponName; ?></option><?php
		} ?>
	</select>&nbsp;&nbsp;
	<input type="submit" value="Add Weapon(s)" />
</form>
<br />

<form method="POST" action="<?php echo $ShipHREF; ?>">
	<select name="ship_type_id"><?php
		foreach ($ShipList as $shipTypeID => $shipName) { ?>
			<option value="<?php echo $shipTypeID; ?>"><?php echo $shipName; ?></option><?php
		} ?>
	</select>&nbsp;&nbsp;
	<input type="submit" value="Change Ship" />
</form>
<br />

<form method="POST" action="<?php echo $HardwareHREF; ?>">
	<input type="number" name="amount_hard" value="0" style="width:75px" />&nbsp;
	<select name="type_hard"><?php
		foreach ($Hardware as $hardwareTypeID => $hardwareName) { ?>
			<option value="<?php echo $hardwareTypeID; ?>"><?php echo $hardwareName; ?></option><?php
		} ?>
	</select>&nbsp;&nbsp;
	<input type="submit" value="Set Hardware" />
</form>
<br />

<form method="POST" action="<?php echo $WarpHREF; ?>">
	<input type="number" name="sector_to" value="<?php echo $ThisPlayer->getSectorID(); ?>" style="width:75px" />&nbsp;&nbsp;
	<input type="submit" value="Warp to Sector" />
</form>
<br />

<form method="POST" action="<?php echo $TurnsHREF; ?>">
	<input type="number" name="turns" value="<?php echo $ThisPlayer->getTurns(); ?>" style="width:75px" />&nbsp;&nbsp;
	<input type="submit" value="Set Turns" />
</form>
<br />

<form method="POST" action="<?php echo $ExperienceHREF; ?>">
	<input type="number" name="exp" value="<?php echo $ThisPlayer->getExperience(); ?>" style="width:75px" />&nbsp;&nbsp;
	<input type="submit" value="Set Experience" />
</form>
<br />

<form method="POST" action="<?php echo $AlignmentHREF; ?>">
	<input type="number" name="align" value="<?php echo $ThisPlayer->getAlignment(); ?>" style="width:75px" />&nbsp;&nbsp;
	<input type="submit" value="Set Alignment" />
</form>
<br />

<form method="POST" action="<?php echo $PersonalRelationsHREF; ?>">
	<input type="number" name="amount" value="0" style="width:75px" />&nbsp;
	<select name="race"><?php
		foreach (Race::getAllNames() as $raceID => $raceName) { ?>
			<option value="<?php echo $raceID; ?>"><?php echo $raceName; ?></option><?php
		} ?>
	</select>&nbsp;&nbsp;
	<input type="submit" value="Set Personal Relations" />
</form>
<br />

<form method="POST" action="<?php echo $RaceRelationsHREF; ?>">
	<input type="number" name="amount" value="0" min="<?php echo MIN_GLOBAL_RELATIONS; ?>" max="<?php echo MAX_GLOBAL_RELATIONS; ?>" style="width:75px" />&nbsp;
	<select name="race"><?php
		foreach (Race::getPlayableNames() as $raceID => $raceName) {
			if ($raceID == $ThisPlayer->getRaceID()) continue; ?>
			<option value="<?php echo $raceID; ?>"><?php echo $raceName; ?></option><?php
		} ?>
	</select>&nbsp;&nbsp;
	<input type="submit" value="Set Political Relations" />
</form>
<br />

<form method="POST" action="<?php echo $ChangeRaceHREF; ?>">
	<select name="race"><?php
		foreach (Race::getPlayableNames() as $raceID => $raceName) {
			if ($raceID == $ThisPlayer->getRaceID()) continue; ?>
			<option value="<?php echo $raceID; ?>"><?php echo $raceName; ?></option><?php
		} ?>
	</select>&nbsp;&nbsp;
	<input type="submit" value="Change Race" />
</form>

<?php
if ($ThisSector->hasPlanet()) { ?>
	<br /><br />
	<h2>Modify Planet</h2>
	<p><a href="<?php echo $MaxBuildingsHREF; ?>">Set buildings to max</a></p>
	<p><a href="<?php echo $MaxDefensesHREF; ?>">Set defenses to max</a></p>
	<p><a href="<?php echo $MaxStockpileHREF; ?>">Set stockpile to max</a></p><?php
}
