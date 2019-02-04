<?php
if($ThisSector->hasPlanet()) {
	$Planet = $ThisSector->getPlanet(); ?>
	<table class="standard csl">
		<tr>
			<th>Planet</th>
			<th>Option</th>
		</tr>
		<tr>
			<td>
				<img align="left" src="<?php echo $Planet->getTypeImage()?>" width="16" height="16" alt="Planet" title="<?php echo $Planet->getTypeName() ?>" />
				&nbsp;<?php echo $Planet->getName() ?>&nbsp;
				<?php echo $Planet->getTypeName() ?>&nbsp;
				<?php if($Planet->isInhabitable()) { ?>
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
					<a class="buttonA" href="<?php echo $Planet->getExamineHREF() ?>">Examine</a>
				</div>
			</td>
		</tr>
	</table><br />
<?php } ?>
