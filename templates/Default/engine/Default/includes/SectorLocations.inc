<?php
if($ThisSector->hasLocation()) { ?>
	<table class="standard csl">
		<tr>
			<th>Location</th>
			<?php
			if($ThisSector->hasAnyLocationsWithAction()) {
				?><th>Option</th><?php
			} ?>
		</tr><?php
		foreach($ThisSector->getLocations() as $Location) { ?>
			<tr>
				<td<?php if(!$Location->hasAction() && $ThisSector->hasAnyLocationsWithAction()){ ?> colspan="2"<?php } ?>>
					<img align="left"src="<?php echo $Location->getImage(); ?>" width="16" height="16" alt="<?php echo $Location->getName(); ?>" title="<?php echo $Location->getName(); ?>" />&nbsp;<?php echo $Location->getName(); ?>
				</td><?php
				if($Location->hasAction()) { ?>
					<td class="shrink noWrap">
						<div class="buttonA"><a class="buttonA" href="<?php echo $Location->getExamineHREF(); ?>">Examine</a></div>
					</td><?php
				} ?>
			</tr><?php
			} ?>
	</table><br /><?php
} ?>
