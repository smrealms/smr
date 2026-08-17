<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Location;
use Smr\Planet;
use Smr\PlanetTypes\PlanetType;
use Smr\Port;
use Smr\Race;
use Smr\Sector;
use Smr\TradeGood;
use Smr\TransactionType;

class EditSectorRenderer {

/**
 * @param list<int> $SectorLocationIDs
 */
public static function render(
	Sector $EditSector,
	int $LastSector,
	string $EditHREF,
	?Planet $Planet,
	?Port $Port,
	array $SectorLocationIDs,
	string $WarpGal,
	int $WarpSectorID,
	string $CancelHREF,
	?string $Message,
	Account $ThisAccount,
): void {
?>
<a href="<?php echo $CancelHREF; ?>">&lt;&lt; Back</a><br /><br />
<form method="POST" action="<?php echo $EditHREF; ?>">
	<h2>Planet</h2><br />
	<b>Type: </b>
	<select name="plan_type">
		<option value="0">No Planet</option><?php
		foreach (array_keys(PlanetType::PLANET_TYPES) as $type) { ?>
			<option value="<?php echo $type; ?>" <?php echo ($type === $Planet?->getTypeID() ? 'selected' : ''); ?>><?php echo PlanetType::getTypeInfo($type)->name(); ?></option><?php
		} ?>
	</select><br /><?php
	if ($Planet !== null) { ?>
		<b>Habitable: </b><?php echo date($ThisAccount->getDateTimeFormat(), $Planet->getInhabitableTime()); ?><br /><?php
	} ?>
	<br />

	<h2>Port</h2>
	<select name="port_level">
		<option value="0">No Port</option><?php
		$MaxPortLevel = Port::getMaxLevelByGame($EditSector->getGameID());
		for ($i = 1; $i <= $MaxPortLevel; $i++) { ?>
			<option value="<?php echo $i; ?>" <?php echo ($i === $Port?->getLevel() ? 'selected' : ''); ?>>Level <?php echo $i; ?></option><?php
		} ?>
	</select>&nbsp;

	<select name="port_race"><?php
		foreach (Race::getAllNames() as $raceID => $raceName) { ?>
			<option value="<?php echo $raceID; ?>" <?php echo ($raceID === $Port?->getRaceID() ? 'selected' : ''); ?>><?php echo $raceName; ?></option><?php
		} ?>
	</select>
	<br />

	<?php
	if ($Port !== null) { ?>
		<br /><span class="bold red">WARNING: </span>Ports should only have (Level + 2) number of goods.
		These options will be ignored if you are changing the port level.
		<input type="hidden" name="select_goods" value="1" />
		<table class="nobord">
			<tr><?php
				foreach ([1, 2, 3] as $class) { ?>
					<td class="top">
					<table class="nobord"><?php
						foreach (TradeGood::getAll() as $goodID => $good) {
							if ($good->class === $class) { ?>
								<tr>
								<td>
									<?php echo $good->getImageHTML(); ?>&nbsp;
									<select name="good<?php echo $goodID; ?>">
										<option value="None">--</option><?php
										foreach (TransactionType::cases() as $trans) { ?>
											<option <?php if ($Port->hasGood($goodID, $trans)) { ?> selected <?php } ?> value="<?php echo $trans->value; ?>"><?php echo $trans->value; ?></option><?php
										} ?>
									</select>
								</td>
								</tr><?php
							}
						} ?>
					</table></td><?php
				} ?>
			</tr>
		</table><?php
	} ?>
	<br />

	<h2>Locations</h2>
	<input type="checkbox" name="add_linked_locs" />
	Automatically add linked locations for Headquarters
	<table class="shrink">
		<tr>
			<td class="center noWrap">
				<?php
				foreach ($SectorLocationIDs as $i => $SectorLocationID) { ?>
					<b><?php echo ($i + 1); ?>. </b>
					<select name="loc_type<?php echo $i; ?>">
						<option value="0">No Location</option><?php
						foreach (Location::getAllLocations($EditSector->getGameID()) as $id => $location) { ?>
							<option value="<?php echo $id ?>" <?php echo ($id === $SectorLocationID ? 'selected' : ''); ?>><?php echo $location->getName(); ?></option><?php
						} ?>
					</select>
					<br /><?php
				} ?>
			</td>

			<td class="center">
				Warp Sector:<br />
				<input type="number" min="0" max="<?php echo $LastSector; ?>" class="center" name="warp" value="<?php echo $WarpSectorID; ?>" style="width:140px" />
				<br /><?php echo $WarpGal; ?>
			</td>
		</tr>
	</table>
	<br /><br />

	<?php echo create_submit_display('Edit Sector'); ?>
	<br /><br />
</form>

<?php
if (isset($Message)) {
	echo $Message;
}

}

}
