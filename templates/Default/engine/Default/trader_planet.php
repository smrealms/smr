<a href="http://wiki.smrealms.de/index.php?title=Planets" target="_blank"><img align="right" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a>
<div align="center"><?php
	if (count($TraderPlanets) > 0) {
		$this->includeTemplate('includes/PlanetList.inc',array('Planets'=>&$TraderPlanets));
	}
	else {
		?>You don't have a planet claimed!<br /><br /><?php
	}

	if($ThisPlayer->hasAlliance()) {
		if (count($AlliancePlanets) > 0) {
			$this->includeTemplate('includes/PlanetList.inc',array('Planets'=>&$AlliancePlanets));
		}
		elseif (count($TraderPlanets) == 0) {
			?>Your alliance has no claimed planets!<?php
		}
		else {
			?>Your planet is the only planet in the alliance!<?php
		}
	}
	?>
</div>