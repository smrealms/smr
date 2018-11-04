<!DOCTYPE html>
<html>
	<head><?php
		$this->includeTemplate('includes/Head.inc');
		if(isset($FocusSector)) { ?>
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
					<td>Map of the known <b><big><?php echo $ThisGalaxy->getName(); ?></big></b> galaxy.</td>
					<td>
						&thinsp;
						<a href="map_warps.php">
							<img src="images/warp_chart.svg" height="24px" width="24px" style="vertical-align: middle;" />&thinsp;Open warp chart
						</a>
					</td>
				</tr>
				<tr>
					<td class="top">
						<form name="GalaxyMapForm" method="GET">
							<label for="galaxy_id">Switch galaxy</label>&nbsp;
							<select name="galaxy_id" id="galaxy_id" class="InputFields" onchange="this.form.submit()"><?php
								foreach ($GameGalaxies as $GameGalaxy) {
									$GalaxyID = $GameGalaxy->getGalaxyID(); ?>
									<option value="<?php echo $GalaxyID; ?>"<?php if($ThisGalaxy->equals($GameGalaxy)){ ?> selected="selected"<?php } ?>>
									<?php echo $GameGalaxy->getName(); ?>
									</option><?php
								} ?>
							</select>&nbsp;
							<input type="submit" value="View"/>
						</form>
						<br />
						<form name="GalaxyMapJumpTo" method="GET">
							<label for="sector_id">Switch sector</label>&nbsp;
							<input type="number" size="5" maxlength="5" name="sector_id" id="sector_id" />&nbsp;
							<input type="submit" value="View" />
						</form>
					</td>
					<td class="bottom">
						<?php $this->includeTemplate('includes/SectorMapOptions.inc'); ?>
					</td>
				</tr>
			</table>
		</div>

		<div class="gal_map_main">
			<?php $this->includeTemplate('includes/SectorMap.inc',array('GalaxyMap'=>true)); ?>
		</div>
	</body>
</html>
