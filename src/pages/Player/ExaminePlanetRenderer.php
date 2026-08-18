<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Account;
use Smr\Pages\Shared\SectorPlayersRenderer;
use Smr\Planet;
use Smr\Player;

class ExaminePlanetRenderer {

	/**
	 * @param array<int, Player> $VisiblePlayers
	 */
	public static function render(
		Planet $ThisPlanet,
		bool $PlanetLand,
		array $VisiblePlayers,
		string $SectorPlayersLabel,
		Account $ThisAccount,
		Player $ThisPlayer,
	): void {
		?>
		<table>
			<tr>
				<td class="bold">Planet Name:</td>
				<td><span id="planet_name"><?php echo $ThisPlanet->getDisplayName(); ?></span></td>
			</tr>
			<tr>
				<td class="bold">Planet Type:</td>
				<td><img class="left" src="<?php echo $ThisPlanet->getTypeImage(); ?>" width="16" height="16" alt="Planet" title="<?php echo $ThisPlanet->getTypeName(); ?>" />&nbsp;<?php echo $ThisPlanet->getTypeName(); ?></td>
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
				<td>
					<span id="planet_owner"><?php
						if ($ThisPlanet->hasOwner()) {
							echo $ThisPlanet->getOwner()->getLinkedDisplayName(false);
						} else { ?>
							Unclaimed<?php
						} ?>
					</span>
				</td>
			</tr>
			<tr>
				<td class="bold">Alliance:</td>
				<td>
					<span id="planet_alliance"><?php
						if ($ThisPlanet->hasOwner()) {
							echo $ThisPlanet->getOwner()->getAllianceDisplayName(true);
						} else { ?>
							none<?php
						} ?>
					</span>
				</td>
			</tr>
			<tr>
				<td class="bold">Defences:</td>
				<td>This planet can repel up to <?php echo $ThisPlanet->getMaxAttackers(); ?> attackers at a time.</td>
			</tr>
			<tr>
				<td class="bold">Landing:</td>
				<td><?php
					if ($ThisPlanet->isMaxLandedUnlimited()) { ?>
						The planetary surface can support an entire armada!<?php
					} else { ?>
						There is only room for <?php echo $ThisPlanet->getMaxLanded(); ?> ships on the surface.<?php
					} ?>
				</td>
			</tr>
		</table>

		<?php
		if ($ThisPlanet->hasPermanentDestruction()) { ?>
			<p class="red">This location is permanently destroyed once its defences are breached!</p><?php
		} ?>

		<br />

		<?php
		SectorPlayersRenderer::render(
			ThisAccount: $ThisAccount,
			ThisPlanet: $ThisPlanet,
			ThisPlayer: $ThisPlayer,
			VisiblePlayers: $VisiblePlayers,
			CloakedPlayers: [],
			SectorPlayersLabel: $SectorPlayersLabel,
		);
		?>

		<div class="center ajax"><?php
			if (!$PlanetLand) { ?>
				<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getAttackHREF(); ?>">Attack Planet (<?php echo TURNS_TO_SHOOT_PLANET; ?>)</a></div><?php
			} elseif ($ThisPlanet->isInhabitable()) { ?>
				<div class="buttonA"><a class="buttonA" href="<?php echo $ThisPlanet->getLandHREF(); ?>">Land on Planet (<?php echo TURNS_TO_LAND; ?>)</a></div><?php
			} else { ?>
				The planet is <span class="uninhab">uninhabitable</span> at this time.<?php
			} ?>
		</div>

		<?php
	}

}
