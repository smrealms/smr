<div id="players_cs" class="ajax">
	<?php if($ThisSector->hasOtherTraders($ThisPlayer))
	{
		$Players =& $ThisSector->getOtherTraders($ThisPlayer);
		if($ThisPlayer->canSeeAny($Players))
		{ ?>
			<table class="standard fullwidth">
				<tr>
					<th style="background: rgb(85, 0, 0) none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;" colspan="5">Ships (<?php echo count($Players) ?>)</th>
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
								<?php echo $Player->getLinkedDisplayName() ?>
							</td>
							<td><?php if($Ship->hasActiveIllusion())
							{
								echo $Ship->getIllusionShipName();
							}
							else
							{
								echo $Ship->getName();
							} ?></td>
							<td class="shrink center nowrap"><?php
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
							<td class="shrink center nowrap"><?php echo $Player->getLevelID() ?></td>
							<td class="shrink center nowrap">
								<div class="buttonA">
									<a href="<?php echo $Player->getExamineTraderHREF() ?>" class="buttonA"> Examine </a>
								</div>
							</td>
						</tr>
					<?php } 
				} ?>
			</table>
		<?php
		}
		else
		{ ?>
			<span class="red bold">WARNING:</span> Sensors have detected the presence of cloaked vessels in this sector<br /><br />
		<?php
		}
	} ?>
</div><br />