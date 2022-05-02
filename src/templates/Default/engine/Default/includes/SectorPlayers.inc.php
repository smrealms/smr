<?php
function getPlayerOptionClass($player, $other) {
	// Returns the CSS relational class of player "other" relative to "player".
	return match (true) {
		$player->traderNAPAlliance($other) => 'friendly',
		$other->canFight() => 'enemy',
		default => 'neutral',
	};
}
?>

<div id="players_cs" class="ajax"><?php
	if (count($VisiblePlayers) > 0) { ?>
		<table class="standard fullwidth csShips">
			<tr>
				<th class="header" colspan="5"><?php echo $SectorPlayersLabel; ?> (<?php echo count($VisiblePlayers) ?>)</th>
			</tr>
			<tr>
				<th>Trader</th>
				<th>Ship</th>
				<th>Rating</th>
				<th>Level</th>
				<th>Option</th>
			</tr>
			<?php
			foreach ($VisiblePlayers as $Player) {
				$Ship = $Player->getShip(); ?>
				<tr<?php if ($Player->hasNewbieStatus()) { ?> class="newbie"<?php } ?>>
					<td>
						<?php echo $Player->getLinkedDisplayName(); ?>
					</td>
					<td><?php
						if ($ThisPlayer->traderMAPAlliance($Player)) {
							if ($Player->isFlagship() && $ThisPlayer->sameAlliance($Player)) { ?>
								<img title="Alliance Flagship" alt="Alliance Flagship" src="images/flagship.png" width="16" height="12" />&nbsp;<?php
							}
							echo $Ship->getName();
							if ($Ship->hasActiveIllusion()) {
								if ($Ship->getName() != $Ship->getIllusionShipName()) {
									?> <span class="npcColour">(<?php echo $Ship->getIllusionShipName(); ?>)</span><?php
								}
							}
						} else {
							echo $Ship->getDisplayName();
						}
						if ($Ship->isCloaked()) {
							?> <span class="red">[Cloaked]</span><?php
						}
						if ($Player->hasCustomShipName() && ($ThisAccount->isDisplayShipImages() || stripos($Player->getCustomShipName(), '<img') === false)) {
							?><br /><?php echo $Player->getCustomShipName();
						} ?>
					</td>
					<td class="shrink center noWrap"><?php
						if ($ThisPlayer->traderMAPAlliance($Player)) {
							echo $Ship->getAttackRating(); ?> / <?php echo $Ship->getDefenseRating();
							if ($Ship->hasActiveIllusion()) {
								?> <span class="npcColour">(<?php echo $Ship->getIllusionAttack(); ?> / <?php echo $Ship->getIllusionDefense(); ?>)</span><?php
							}
						} else {
							echo $Ship->getDisplayAttackRating(); ?> / <?php echo $Ship->getDisplayDefenseRating();
						} ?>
					</td>
					<td class="shrink center noWrap"><?php echo $Player->getLevelID() ?></td>
					<td class="shrink center noWrap">
						<div class="buttonA"><?php
							if ($ThisPlayer->isLandedOnPlanet()) {
								if ($ThisPlanet->getOwnerID() == $ThisPlayer->getAccountID()) {
									?><a href="<?php echo $Player->getPlanetKickHREF() ?>" class="<?php
										echo getPlayerOptionClass($ThisPlayer, $Player);
										?>"> Kick </a><?php
								}
							} else {
								?><a href="<?php echo $Player->getExamineTraderHREF() ?>" class="<?php
									echo getPlayerOptionClass($ThisPlayer, $Player);
								?>"> Examine </a><?php
							} ?>
						</div>
					</td>
				</tr><?php
			} ?>
		</table><?php
	}
	if (isset($CloakedPlayers) && count($CloakedPlayers) > 0) {
		?><span class="red bold">WARNING:</span> Sensors have detected the presence of cloaked vessels in this sector<?php
	} ?>
</div><br />
