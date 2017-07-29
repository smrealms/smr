<div align="center">
	<?php
	if (count($AlliancePlanets) > 0) { ?>
	Your alliance currently has <span id="numplanets"><?php echo count($AlliancePlanets); ?></span> planets in the universe!<br /><br /><?php
		$this->includeTemplate('includes/PlanetList.inc',array('Planets'=>&$AlliancePlanets));
	}
	else { ?>
		Your alliance has no claimed planets.
		<a href="<?php echo WIKI_URL; ?>/index.php?title=Planets" target="_blank"><img align="right" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a>
		<?php
	} ?>
</div>
