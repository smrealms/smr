<?php if($ThisSector->hasLocation())
{
	$Locations =& $ThisSector->getLocations() ?>
	<table cellspacing="0" cellpadding="0" class="standard csl">
		<tr>
			<th>Location</th>
			<?php
			if($ThisSector->hasAnyLocationsWithAction())
			{
				echo '<th>Option</th>';
			} ?>
		</tr>
		<?php
		foreach($Locations as $Location)
		{ ?>
			<tr>
				<td<?php if(!$Location->hasAction() && $ThisSector->hasAnyLocationsWithAction()) echo 'colspan="2"'; ?>>
					<img align="left"src="<?php echo $Location->getImage() ?>" alt="<?php echo $Location->getName() ?>" title="<?php echo $Location->getName() ?>" /> <?php echo $Location->getName() ?>
				</td>
				<?php if($Location->hasAction())
				{ ?> 
					<td class="shrink nowrap">
						<div class="buttonA"><a class="buttonA" href="<?php echo $Location->getExamineHREF() ?>">&nbsp;Examine&nbsp;</a></div>
					</td>
				<?php
				} ?>
			</tr>
		<?php } ?>
	</table><br />
<?php } ?>