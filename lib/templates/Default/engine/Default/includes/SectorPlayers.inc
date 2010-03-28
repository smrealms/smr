<div id="players_cs" class="ajax"><?php
	if($ThisSector->hasOtherTraders($ThisPlayer))
	{
		$Players =& $ThisSector->getOtherTraders($ThisPlayer);
		if($ThisPlayer->canSeeAny($Players))
		{ ?>
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
				foreach($Players as $Player)
				{
					if($ThisPlayer->canSee($Player))
					{
						$Ship =& $Player->getShip();?>
						<tr<?php if($Player->getAccount()->isNewbie()) { ?> class="newbie"<?php } ?>>
							<td>
								<?php echo $Player->getLinkedDisplayName(); ?>
							</td>
							<td><?php
								if($Player->hasCustomShipName()&&($ThisAccount->isDisplayShipImages()||stripos($Player->getCustomShipName(),'<img')===false))
								{
									echo $Player->getCustomShipName();
								}
								if($Ship->hasActiveIllusion())
								{
									echo $Ship->getIllusionShipName();
								}
								else
								{
									echo $Ship->getName();
								}
								if($Ship->isCloaked())
								{
									?> <span class="red">[Cloaked]</span><?php
								} ?>
							</td>
							<td class="shrink center noWrap"><?php
								if($Ship->hasActiveIllusion())
								{
									echo $Ship->getIllusionAttack();
								}
								else
								{
									echo $Ship->getAttackRating();
								}
								echo ' / ';
								if($Ship->hasActiveIllusion())
								{
									echo $Ship->getIllusionDefense();
								}
								else
								{
									echo $Ship->getDefenseRating();
								} ?></td>
							<td class="shrink center noWrap"><?php echo $Player->getLevelID() ?></td>
							<td class="shrink center noWrap">
								<div class="buttonA">
									<a href="<?php echo $Player->getExamineTraderHREF() ?>" class="buttonA<?php if(!$ThisPlayer->traderNAPAlliance($Player)){ ?> enemyExamine<?php } ?>"> Examine </a>
								</div>
							</td>
						</tr><?php
					} 
				} ?>
			</table><?php
		}
		else
		{
			?><span class="red bold">WARNING:</span> Sensors have detected the presence of cloaked vessels in this sector<?php
		}
	} ?>
</div><br />