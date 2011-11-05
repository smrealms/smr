<div align="center">
	<?php
	if (count($AlliancePlanets) > 0)
	{ ?>
		Your alliance currently has <?php echo count($AlliancePlanets); ?> planets in the universe!<br /><br /><?php
		$this->includeTemplate('includes/PlanetList.inc',array('Planets'=>&$AlliancePlanets));
	}
	else
	{ ?>
		Your alliance has no claimed planets<?php
	} ?>
</div>