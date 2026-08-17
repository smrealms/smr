<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Pages\Shared\Admin\Unigen\GalaxyDetailsRenderer;

class EditGalaxiesRenderer {

/**
 * @param array{value: string, href: string} $Submit
 * @param array<int, array{Name: string, Width: int, Height: int, Type: string, ForceMaxHours: float, DelHREF: string}> $Galaxies
 */
public static function render(bool $GameEnabled, array $Submit, array $Galaxies, string $BackHREF, string $AddHREF, int $MaxAddId): void {
?>

<?php GalaxyDetailsRenderer::render(Galaxies: $Galaxies, GameEnabled: $GameEnabled, Submit: $Submit); ?>

<?php
if ($GameEnabled) { ?>
	<p>
		<span class="bold">NOTE: </span>Galaxy sizes cannot be changed because
		this game has already been enabled.
	</p><?php
} else { ?>
	<p>
		<span class="red bold">WARNING: </span>If you modify galaxy sizes,
		any ports, planets, and locations in sectors that are removed will also
		be removed! Relative locations may also shift.
	</p>
	<br />

	<form method=POST action="<?php echo $AddHREF; ?>">
		Add new galaxy ID: <input required name="insert_galaxy_id" type="number" min="1" max="<?php echo $MaxAddId; ?>" /> <?php echo create_submit_display('Insert'); ?>
	</form><?php
} ?>

<br />
<a href="<?php echo $BackHREF; ?>">&lt;&lt; Back</a>

<?php
}

}
