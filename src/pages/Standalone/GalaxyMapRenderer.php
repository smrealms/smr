<?php declare(strict_types=1);

namespace Smr\Pages\Standalone;

use Smr\Galaxy;
use Smr\Pages\Layout\HeadRenderer;
use Smr\Pages\Shared\SectorMapOptionsRenderer;
use Smr\Pages\Shared\SectorMapRenderer;
use Smr\Player;

class GalaxyMapRenderer {

/**
 * @param array<int, \Smr\Galaxy> $GameGalaxies
 * @param array<int, array<int, \Smr\Sector>> $MapSectors
 */
public static function render(
	Galaxy $ThisGalaxy,
	array $GameGalaxies,
	int $LastSector,
	?int $FocusSector,
	bool $HideAlliedForces,
	bool $ShowSeedlistSectors,
	?string $CheckboxFormHREF,
	Player $ThisPlayer,
	array $MapSectors,
): void {
?>
<!DOCTYPE html>
<html>
	<head><?php
		HeadRenderer::render($ThisPlayer->getAccount(), $ThisPlayer->getGame()->getName());
		if (isset($FocusSector)) { ?>
			<script>
				$(function() {
					var focusSector = $('#sector<?php echo $FocusSector; ?>'),
						body = $('html, body'),
						offset = focusSector.offset();
					body.scrollTop(offset.top + focusSector.height() / 2 - window.innerHeight / 2);
					body.scrollLeft(offset.left + focusSector.width() / 2 - window.innerWidth / 2);
				});
			</script><?php
		} ?>
	</head>

	<body>
		<div class="gal_map_header">
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td>Map of the known <span class="big bold"><?php echo $ThisGalaxy->getDisplayName(); ?></span> galaxy.</td>
					<td>
						&thinsp;
						<a href="map_warps.php?game=<?php echo $ThisGalaxy->getGameID(); ?>">
							<img src="images/warp_chart.svg" height="24" width="24" style="vertical-align: middle;" />&thinsp;Open warp chart
						</a>
					</td>
				</tr>
				<tr>
					<td class="top">
						<form name="GalaxyMapForm" method="GET">
							<label for="galaxy_id">Switch galaxy</label>&nbsp;
							<select name="galaxy_id" id="galaxy_id" onchange="this.form.submit()"><?php
								foreach ($GameGalaxies as $GameGalaxy) {
									$GalaxyID = $GameGalaxy->getGalaxyID(); ?>
									<option value="<?php echo $GalaxyID; ?>"<?php if ($ThisGalaxy->equals($GameGalaxy)) { ?> selected="selected"<?php } ?>>
									<?php echo $GameGalaxy->getDisplayName(); ?>
									</option><?php
								} ?>
							</select>&nbsp;
							<?php echo create_submit_display('View'); ?>
						</form>
						<br />
						<form name="GalaxyMapJumpTo" method="GET">
							<label for="sector_id">Switch sector</label>&nbsp;
							<input type="number" min="1" max="<?php echo $LastSector; ?>" required name="sector_id" id="sector_id" />&nbsp;
							<?php echo create_submit_display('View'); ?>
						</form>
					</td>
					<td class="bottom">
						<?php SectorMapOptionsRenderer::render(
							CheckboxFormHREF: $CheckboxFormHREF,
							HideAlliedForces: $HideAlliedForces,
							ShowSeedlistSectors: $ShowSeedlistSectors,
						); ?>
					</td>
				</tr>
			</table>
		</div>

		<div class="gal_map_main">
			<?php SectorMapRenderer::render(
				GalaxyMap: true,
				ThisPlayer: $ThisPlayer,
				HideAlliedForces: $HideAlliedForces,
				ShowSeedlistSectors: $ShowSeedlistSectors,
				MapSectors: $MapSectors,
			); ?>
		</div>
	</body>
</html>

<?php
}

}
