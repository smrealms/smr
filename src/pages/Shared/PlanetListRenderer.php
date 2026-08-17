<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Alliance;
use Smr\Planet;
use Smr\Player;
use Smr\Template;

class PlanetListRenderer {

/**
 * @param array<\Smr\Planet> $AllPlanets
 */
public static function render(
	Template $template,
	?Alliance $Alliance,
	array $AllPlanets,
	?Planet $PlayerPlanet,
	Player $ThisPlayer,
	bool $Financial,
): void {
?>
<div class="center">
	<?php
	if (isset($PlayerPlanet)) { ?>
		You own the planet in sector <a href="#planet-<?php echo $PlayerPlanet->getSectorID(); ?>" target="_self">#<?php echo $PlayerPlanet->getSectorID(); ?></a>.<br /><?php
	}

	if (count($AllPlanets) === 0) {
		if ($Alliance === null) { ?>
			You do not own a planet!
			<a href="<?php echo WIKI_URL; ?>/game-guide/locations#planets" target="_blank"><img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a>
			<?php
		} else { ?>
			<?php echo $Alliance->getAllianceDisplayName(true); ?> has no claimed planets.
			<a href="<?php echo WIKI_URL; ?>/game-guide/locations#planets" target="_blank"><img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Planets"/></a>
			<?php
		}
	} else {
		if ($Alliance !== null) { ?>
			<?php echo $Alliance->getAllianceDisplayName(true); ?> currently has <span id="numplanets"><?php echo pluralise(count($AllPlanets), 'planet'); ?></span> in the universe!<br /><br /><?php
		}
		if ($Financial) {
			PlanetListFinancialRenderer::render(
				template: $template,
				Planets: $AllPlanets,
				ThisPlayer: $ThisPlayer,
			);
		} else {
			PlanetListDefenseRenderer::render(
				template: $template,
				Planets: $AllPlanets,
				ThisPlayer: $ThisPlayer,
			);
		}
	} ?>
</div>

<?php
}

}
