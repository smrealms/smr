<?php
if($ThisSector->hasPlanet()) {
	$planet =& $ThisSector->getPlanet(); ?>
	<table class="standard csl">
		<tr>
			<th>Planet</th>
			<th>Option</th>
		</tr>
		<tr>
			<td>
				<div style="display: inline; padding: 0; margins: 0;"
					data-img="../<?php echo $planet->getTypeImage(); ?>" 
					data-tip="<?php echo $planet->getTypeName().': '.$planet->getName(); ?>">
					<img alt="Planet" src="images/blank_16.png" 
					style="background-image: url('<?php echo $planet->getTypeImage(); ?>');"  
					class="planets planett<?php echo $planet->getTypeID(); ?>"/>
				</div>&nbsp;<?php echo $planet->getName() ?>&nbsp;
				<?php echo $planet->getTypeName() ?>&nbsp;
				<?php if($planet->isInhabitable()) { ?>
					<span class="inhab">Inhabitable</span>
				<?php 
				}
				else { ?>
					<span class="uninhab">Uninhabitable</span>
				<?php
				} ?>
			</td>
			<td class="center noWrap shrink">
				<div class="buttonA">
					<a class="buttonA" href="<?php echo $planet->getExamineHREF() ?>">&nbsp;Examine&nbsp;</a>
				</div>
			</td>
		</tr>
	</table><br />
<?php } ?>