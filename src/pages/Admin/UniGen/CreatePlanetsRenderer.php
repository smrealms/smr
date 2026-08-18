<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Galaxy;

class CreatePlanetsRenderer {

	/**
	 * @param array<int, Galaxy> $Galaxies
	 * @param array<int, string> $AllowedTypes
	 * @param array<int, int> $NumberOfPlanets
	 */
	public static function render(
		array $Galaxies,
		string $JumpGalaxyHREF,
		array $AllowedTypes,
		Galaxy $Galaxy,
		array $NumberOfPlanets,
		string $CreatePlanetsFormHREF,
		string $CancelHREF,
	): void {
		?>
		<form method="POST" action="<?php echo $JumpGalaxyHREF; ?>">
			Working on Galaxy:
			<select name="gal_on" onchange="this.form.submit()"><?php
				foreach ($Galaxies as $OtherGalaxy) { ?>
					<option value="<?php echo $OtherGalaxy->getGalaxyID(); ?>"<?php if ($OtherGalaxy->equals($Galaxy)) { ?> selected<?php } ?>><?php
						echo $OtherGalaxy->getDisplayName() . ' (' . $OtherGalaxy->getGalaxyID() . ')'; ?>
					</option><?php
				} ?>
			</select>
		</form>
		<br />

		<form method="POST" action="<?php echo $CreatePlanetsFormHREF; ?>">
			<table class="standard">
				<tr>
					<th>Planet Type</th>
					<th>Amount</th>
				</tr><?php
				foreach ($AllowedTypes as $ID => $Name) { ?>
					<tr>
						<td class="right"><?php echo $Name; ?></td>
						<td><input class="center" type="number" value="<?php echo $NumberOfPlanets[$ID]; ?>" size="5" name="type<?php echo $ID; ?>"></td>
					</tr><?php
				} ?>
				<tr>
					<td colspan="2" class="center">
						<?php echo create_submit_display('Create Planets'); ?>
						<br /><br />
						<a href="<?php echo $CancelHREF; ?>" class="submitStyle">Cancel</a>
					</td>
				</tr>
			</table>
		</form>

		<span class="small">Note: When you press "Create Planets" this will rearrange all current planets.<br />
		To add new planets without rearranging everything use the edit sector feature.</span>

		<?php
	}

}
