<?php
if($ThisSector->hasPlanet())
{
	$Planet =& $ThisSector->getPlanet(); ?>
	<table class="standard csl">
		<tr>
			<th>Planet</th>
			<th>Option</th>
		</tr>
		<tr>
			<td>
				<img align="left" src="images/planet.gif" alt="Planet" title="Planet" />
				&nbsp;<?php echo $Planet->getName() ?>&nbsp;
				<?php if($Planet->isInhabitable())
				{ ?>
					<span class="green">Inhabitable</span>
				<?php 
				}
				else
				{ ?>
					<span class="red">Uninhabitable</span>
				<?php
				} ?>
			</td>
			<td class="center noWrap shrink">
				<div class="buttonA">
					<a class="buttonA" href="<?php echo $Planet->getExamineHREF() ?>">&nbsp;Examine&nbsp;</a>
				</div>
			</td>
		</tr>
	</table><br />
<?php } ?>