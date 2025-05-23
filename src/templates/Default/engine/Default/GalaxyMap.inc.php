<?php declare(strict_types=1);

/**
 * @var Smr\Galaxy $ThisGalaxy
 * @var Smr\Template $this
 * @var array<int, Smr\Galaxy> $GameGalaxies
 * @var int $LastSector
 */

?>
<!DOCTYPE html>
<html>
	<head><?php
		$this->includeTemplate('includes/Head.inc.php');
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
							<input type="submit" value="View"/>
						</form>
						<br />
						<form name="GalaxyMapJumpTo" method="GET">
							<label for="sector_id">Switch sector</label>&nbsp;
							<input type="number" min="1" max="<?php echo $LastSector; ?>" required name="sector_id" id="sector_id" />&nbsp;
							<input type="submit" value="View" />
						</form>
					</td>
					<td class="bottom">
						<?php $this->includeTemplate('includes/SectorMapOptions.inc.php'); ?>
					</td>
				</tr>
			</table>
		</div>

		<div class="gal_map_main">
			<?php $this->includeTemplate('includes/SectorMap.inc.php', ['GalaxyMap' => true]); ?>
		</div>
	</body>
</html>
