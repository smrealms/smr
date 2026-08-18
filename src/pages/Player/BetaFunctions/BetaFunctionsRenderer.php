<?php declare(strict_types=1);

namespace Smr\Pages\Player\BetaFunctions;

use Smr\Player;
use Smr\Race;
use Smr\Sector;

class BetaFunctionsRenderer {

	/**
	 * @param array<int, string> $ShipList
	 * @param array<int, string> $WeaponList
	 * @param array<int, string> $Hardware
	 */
	public static function render(
		string $MapHREF,
		string $MoneyHREF,
		string $ShipHREF,
		array $ShipList,
		string $AddWeaponHREF,
		array $WeaponList,
		string $RemoveWeaponsHREF,
		string $UnoHREF,
		string $WarpHREF,
		string $TurnsHREF,
		string $ExperienceHREF,
		string $AlignmentHREF,
		string $HardwareHREF,
		array $Hardware,
		string $PersonalRelationsHREF,
		string $RaceRelationsHREF,
		string $ChangeRaceHREF,
		?string $MaxBuildingsHREF,
		?string $MaxDefensesHREF,
		?string $MaxStockpileHREF,
		Player $ThisPlayer,
		Sector $ThisSector,
	): void {
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
			<?php echo create_submit_display('Add Weapon(s)'); ?>
		</form>
		<br />

		<form method="POST" action="<?php echo $ShipHREF; ?>">
			<select name="ship_type_id"><?php
				foreach ($ShipList as $shipTypeID => $shipName) { ?>
					<option value="<?php echo $shipTypeID; ?>"><?php echo $shipName; ?></option><?php
				} ?>
			</select>&nbsp;&nbsp;
			<?php echo create_submit_display('Change Ship'); ?>
		</form>
		<br />

		<form method="POST" action="<?php echo $HardwareHREF; ?>">
			<input type="number" name="amount_hard" value="0" style="width:75px" />&nbsp;
			<select name="type_hard"><?php
				foreach ($Hardware as $hardwareTypeID => $hardwareName) { ?>
					<option value="<?php echo $hardwareTypeID; ?>"><?php echo $hardwareName; ?></option><?php
				} ?>
			</select>&nbsp;&nbsp;
			<?php echo create_submit_display('Set Hardware'); ?>
		</form>
		<br />

		<form method="POST" action="<?php echo $WarpHREF; ?>">
			<input type="number" name="sector_to" value="<?php echo $ThisPlayer->getSectorID(); ?>" style="width:75px" />&nbsp;&nbsp;
			<?php echo create_submit_display('Warp to Sector'); ?>
		</form>
		<br />

		<form method="POST" action="<?php echo $TurnsHREF; ?>">
			<input type="number" name="turns" value="<?php echo $ThisPlayer->getTurns(); ?>" style="width:75px" />&nbsp;&nbsp;
			<?php echo create_submit_display('Set Turns'); ?>
		</form>
		<br />

		<form method="POST" action="<?php echo $ExperienceHREF; ?>">
			<input type="number" name="exp" value="<?php echo $ThisPlayer->getExperience(); ?>" style="width:75px" />&nbsp;&nbsp;
			<?php echo create_submit_display('Set Experience'); ?>
		</form>
		<br />

		<form method="POST" action="<?php echo $AlignmentHREF; ?>">
			<input type="number" name="align" value="<?php echo $ThisPlayer->getAlignment(); ?>" style="width:75px" />&nbsp;&nbsp;
			<?php echo create_submit_display('Set Alignment'); ?>
		</form>
		<br />

		<form method="POST" action="<?php echo $PersonalRelationsHREF; ?>">
			<input type="number" name="amount" value="0" min="<?php echo MIN_PERSONAL_RELATIONS; ?>" max="<?php echo MAX_PERSONAL_RELATIONS; ?>" style="width:75px" />&nbsp;
			<select name="race"><?php
				foreach (Race::getAllNames() as $raceID => $raceName) { ?>
					<option value="<?php echo $raceID; ?>"><?php echo $raceName; ?></option><?php
				} ?>
			</select>&nbsp;&nbsp;
			<?php echo create_submit_display('Set Personal Relations'); ?>
		</form>
		<br />

		<form method="POST" action="<?php echo $RaceRelationsHREF; ?>">
			<input type="number" name="amount" value="0" min="<?php echo MIN_POLITICAL_RELATIONS; ?>" max="<?php echo MAX_POLITICAL_RELATIONS; ?>" style="width:75px" />&nbsp;
			<select name="race"><?php
				foreach (Race::getPlayableNames() as $raceID => $raceName) {
					if ($raceID === $ThisPlayer->getRaceID()) continue; ?>
					<option value="<?php echo $raceID; ?>"><?php echo $raceName; ?></option><?php
				} ?>
			</select>&nbsp;&nbsp;
			<?php echo create_submit_display('Set Political Relations'); ?>
		</form>
		<br />

		<form method="POST" action="<?php echo $ChangeRaceHREF; ?>">
			<select name="race"><?php
				foreach (Race::getPlayableNames() as $raceID => $raceName) {
					if ($raceID === $ThisPlayer->getRaceID()) continue; ?>
					<option value="<?php echo $raceID; ?>"><?php echo $raceName; ?></option><?php
				} ?>
			</select>&nbsp;&nbsp;
			<?php echo create_submit_display('Change Race'); ?>
		</form>

		<?php
		if ($ThisSector->hasPlanet()) { ?>
			<br /><br />
			<h2>Modify Planet</h2>
			<p><a href="<?php echo $MaxBuildingsHREF; ?>">Set buildings to max</a></p>
			<p><a href="<?php echo $MaxDefensesHREF; ?>">Set defenses to max</a></p>
			<p><a href="<?php echo $MaxStockpileHREF; ?>">Set stockpile to max</a></p><?php
		}

	}

}
