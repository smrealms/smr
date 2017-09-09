<?php
function getPlayerOptionClass(&$player, &$other) {
	// Returns the CSS relational class of player "other" relative to "player".
	if ($other->canFight()) {
		if ($player->traderNAPAlliance($other)) {
			return "friendly";
		} else {
			return "enemy";
		}
	} else {
		return "neutral";
	}
}
?>

<div id="players_cs" class="ajax"><?php
	if($PlayersContainer->hasOtherTraders($ThisPlayer)) {
		$Players =& $PlayersContainer->getOtherTraders($ThisPlayer);
		if(!($PlayersContainer instanceof SmrSector) || $ThisPlayer->canSeeAny($Players)) { ?>
			<table class="standard fullwidth csShips">
				<tr>
					<th class="header" colspan="5">Ships (<?php echo count($Players) ?>)</th>
				</tr>
				<tr>
					<th>Trader</th>
					<th>Ship</th>
					<th>Rating</th>
					<th>Level</th>
					<th>Option</th>
				</tr>
				<?php
				foreach($Players as &$Player) {
					if(!($PlayersContainer instanceof SmrSector) || $ThisPlayer->canSee($Player)) {
						$Ship =& $Player->getShip();?>
						<tr<?php if($Player->getAccount()->isNewbie()) { ?> class="newbie"<?php } ?>>
							<td>
								<?php echo $Player->getLinkedDisplayName(); ?>
							</td>
							<td><?php
								if($Player->hasCustomShipName()&&($ThisAccount->isDisplayShipImages()||stripos($Player->getCustomShipName(),'<img')===false)) {
									echo $Player->getCustomShipName();
								}
								if($ThisPlayer->traderMAPAlliance($Player) || !$Ship->hasActiveIllusion()) {
									echo $Ship->getName();
								}
								if($Ship->hasActiveIllusion()) {
									if(!$ThisPlayer->traderMAPAlliance($Player)) {
										echo $Ship->getIllusionShipName();
									}
									else if($Ship->getName() != $Ship->getIllusionShipName()) {
										?> <span class="npcColour">(<?php echo $Ship->getIllusionShipName(); ?>)</span><?php
									}
								}
								if($Ship->isCloaked()) {
									?> <span class="red">[Cloaked]</span><?php
								} ?>
							</td>
							<td class="shrink center noWrap"><?php
								if($ThisPlayer->traderMAPAlliance($Player) || !$Ship->hasActiveIllusion()) {
									echo $Ship->getAttackRating(); ?> / <?php echo $Ship->getDefenseRating();
								}
								if($Ship->hasActiveIllusion()) {
									if(!$ThisPlayer->traderMAPAlliance($Player)) {
										echo $Ship->getIllusionAttack(); ?> / <?php echo $Ship->getIllusionDefense();
									}
									else if($Ship->getAttackRating() != $Ship->getIllusionAttack() || $Ship->getDefenseRating() != $Ship->getIllusionDefense()) {
										?> <span class="npcColour">(<?php echo $Ship->getIllusionAttack(); ?> / <?php echo $Ship->getIllusionDefense(); ?>)</span><?php
									}
								} ?></td>
							<td class="shrink center noWrap"><?php echo $Player->getLevelID() ?></td>
							<td class="shrink center noWrap">
								<div class="buttonA"><?php
									if($PlayersContainer instanceof SmrPlanet) {
										if($ThisPlanet->getOwnerID() == $ThisPlayer->getAccountID()) {
											?><a href="<?php echo $Player->getPlanetKickHREF() ?>" class="<?php 
												echo getPlayerOptionClass($ThisPlayer, $Player);
												?>"> Kick </a><?php
										}
									}
									else {
										?><a href="<?php echo $Player->getExamineTraderHREF() ?>" class="<?php 
											echo getPlayerOptionClass($ThisPlayer, $Player);
										?>"> Examine </a><?php
									} ?>
								</div>
							</td>
						</tr><?php
					}
				} ?>
			</table><?php
		}
		else {
			?><span class="red bold">WARNING:</span> Sensors have detected the presence of cloaked vessels in this sector<?php
		}
	} ?>
</div><br />
