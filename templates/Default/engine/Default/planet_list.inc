<div class="center">
	<?php
	if (isset($PlayerPlanet)) { ?>
		You own the planet in sector <a href="#planet-<?php echo $PlayerPlanet->getSectorID(); ?>" target="_self">#<?php echo $PlayerPlanet->getSectorID(); ?></a>.<br /><?php
	}

	if (count($AllPlanets) == 0) {
		if ($PlayerOnly) { ?>
			You do not own a planet!
			<a href="<?php echo WIKI_URL; ?>/game-guide/locations#planets" target="_blank"><img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a>
			<?php
		} else { ?>
			<?php echo $Alliance->getAllianceName(true); ?> has no claimed planets.
			<a href="<?php echo WIKI_URL; ?>/game-guide/locations#planets" target="_blank"><img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a>
			<?php
		}
	} else {
		if (!$PlayerOnly) { ?>
			<?php echo $Alliance->getAllianceName(true); ?> currently has <span id="numplanets"><?php echo count($AllPlanets); ?></span> <?php echo pluralise('planet', count($AllPlanets)); ?> in the universe!<br /><br /><?php
		}
		$this->includeTemplate($ExtraInclude, array('Planets'=>$AllPlanets));
	} ?>
</div>
