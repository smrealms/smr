<?php
if($ThisSector->hasLocation()) {
	$Locations =& $ThisSector->getLocations() ?>
	<table class="standard csl">
		<tr>
			<th>Location</th>
			<?php
			if($ThisSector->hasAnyLocationsWithAction()) {
				?><th>Option</th><?php
			} ?>
		</tr><?php
		foreach($Locations as $Location) { ?>
			<tr>
				<td<?php if(!$Location->hasAction() && $ThisSector->hasAnyLocationsWithAction()){ ?> colspan="2"<?php } ?>>
					<div style="" 
						data-img="../<?php echo $Location->getImage(); ?>"
						data-tip="<?php echo $Location->getName(); ?>">
						<img align="left" src="images/blank_16.png" 
						style="background-image: url('<?php echo $Location->getImage(); ?>');" 
						class="locations locationt<?php echo (int)($Location->getTypeID()/100); ?> location<?php echo $Location->getTypeID(); ?>" 
						alt="<?php echo $Location->getName(); ?>"/> 
					</div>
					&nbsp;<?php echo $Location->getName(); ?>
				</td><?php
				if($Location->hasAction()) { ?>
					<td class="shrink noWrap">
						<div class="buttonA"><a class="buttonA" href="<?php echo $Location->getExamineHREF(); ?>">&nbsp;Examine&nbsp;</a></div>
					</td><?php
				} ?>
			</tr><?php
			} ?>
	</table><br /><?php
} ?>