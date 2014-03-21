<table>
	<tr>
		<td class="bold">Planet Name:</td>
		<td><?php echo $ThisPlanet->getName(); ?></td>
	</tr>
	<tr>
		<td class="bold">Planet Type:</td>
		<td>
		<div style="display: inline; padding: 0; margins: 0;"
			data-img="../<?php echo $ThisPlanet->getTypeImage(); ?>" 
			data-tip="<?php echo $ThisPlanet->getTypeName().': '.$ThisPlanet->getName(); ?>">
			<img alt="Planet" src="images/blank_16.png" 
			style="background-image: url('<?php echo $ThisPlanet->getTypeImage(); ?>');"  
			class="planets planett<?php echo $ThisPlanet->getTypeID(); ?>"/>
		</div>
		<?php echo $ThisPlanet->getTypeName() ?>:
		<?php if($ThisPlanet->isInhabitable()) { ?>
			<span class="inhab">Inhabitable</span>
		<?php 
		}
		else { ?>
			<span class="uninhab">Uninhabitable</span>
		<?php
		} ?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><?php echo $ThisPlanet->getTypeDescription(); ?></td>
	</tr>
	<tr>
		<td class="bold">Level:</td>
		<td><?php echo number_format($ThisPlanet->getLevel(), 2); ?></td>
	</tr>
	<tr>
		<td class="bold">Owner:</td>
		<td><?php
			if ($ThisPlanet->hasOwner()) {
				echo $ThisPlanet->getOwner()->getLinkedDisplayName(false);
			}
			else { ?>
				Unclaimed<?php
			} ?>
		</td>
	</tr>
	<tr>
		<td class="bold">Alliance:</td>
		<td><?php
			if ($ThisPlanet->hasOwner()) { ?>
				<a href="<?php echo $ThisPlanet->getOwner()->getAllianceRosterHREF(); ?>"><?php echo $ThisPlanet->getOwner()->getAllianceName(); ?></a><?php
			}
			else { ?>
				none<?php
			} ?>
		</td>
	</tr>
	<tr>
		<td class="bold">Defences:</td>
		<td>This planet can repel up to <?php echo $ThisPlanet->getMaxAttackers(); ?> attackers at a time.</td>
	</tr>
	<tr>
		<td class="bold">Landing:</td>
		<td><?php
			if ($ThisPlanet->getMaxLanded() == 0) { ?>
				The planetary surface can support an entire armada!<?php
			}
			else { ?>
				There is only room for <?php echo $ThisPlanet->getMaxLanded(); ?> ships on the surface.<?php
			} ?>
		</td>
	</tr>
</table>

<div align="center"><?php
	if (!$PlanetLand) { ?>
		<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getAttackHREF(); ?>">&nbsp;Attack Planet (3)&nbsp;</a></div><?php
	}
	elseif ($ThisPlanet->isInhabitable()) { ?>
		<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getLandHREF(); ?>">&nbsp;Land on Planet (1)&nbsp;</a></div><?php
	}
	else { ?>
		The planet is <span class="uninhab">uninhabitable</span> at this time.<?php
	} ?>
</div>
