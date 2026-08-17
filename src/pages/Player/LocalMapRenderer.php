<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Pages\Shared\SectorMapOptionsRenderer;
use Smr\Pages\Shared\SectorMapRenderer;
use Smr\Player;

class LocalMapRenderer {

/**
 * @param array<int, array<int, \Smr\Sector>> $MapSectors
 */
public static function render(
	bool $ShowSeedlistSectors,
	bool $HideAlliedForces,
	?string $CheckboxFormHREF,
	string $MapExpandHREF,
	string $MapShrinkHREF,
	string $GalaxyName,
	array $MapSectors,
	?Player $ThisPlayer = null,
): void {
?>
<table class="nobord fullwidth">
	<tr>
		<td style="width: 10%" class="top">
			<a href="<?php echo $MapExpandHREF; ?>">
				<img class="bottom" src="images/zoom_expand.svg" width="16" height="16" title="Expand Map" />
			</a>&nbsp;
			<a href="<?php echo $MapShrinkHREF; ?>">
				<img class="bottom" src="images/zoom_shrink.svg" width="16" height="16" title="Shrink Map" />
			</a>
		<td style="width: 80%" class="center">
			Local Map of the Known <span class="big bold"><?php echo $GalaxyName ?></span> Galaxy
			<br /><br />
		</td>
		<td style="width: 10%"></td>
	</tr>
</table>

<?php
SectorMapRenderer::render(
	ThisPlayer: $ThisPlayer,
	MapSectors: $MapSectors,
	GalaxyMap: false,
	HideAlliedForces: $HideAlliedForces,
	ShowSeedlistSectors: $ShowSeedlistSectors,
);
SectorMapOptionsRenderer::render(
	HideAlliedForces: $HideAlliedForces,
	ShowSeedlistSectors: $ShowSeedlistSectors,
	CheckboxFormHREF: $CheckboxFormHREF,
);

}

}
