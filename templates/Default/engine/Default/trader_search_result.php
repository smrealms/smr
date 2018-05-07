<?php
if (!isset($ResultPlayerLinks) && !isset($SimilarPlayersLinks)) {
	echo "No trader found matching your search!";
}

function DisplayResult(array $Links, SmrPlayer $Player) { ?>
	<table class="standard" width="88%">
		<tr>
			<th>Name</th>
			<th>Alliance</th>
			<th>Race</th>
			<th>Experience</th>
			<th>Newbie</th>
			<th>Online</th><?php
			if (in_array($Player->getAccountID(), Globals::getHiddenPlayers())) { ?>
				<th>Sector</th><?php
			} ?>
			<th>Option</th>
		</tr><?php
		foreach ($Links as $Link) { ?>
			<tr>
				<td>
					<a href="<?php echo $Link['SearchHREF']; ?>"><?php echo $Link['Player']->getDisplayName(); ?></a>
					<br /><?php
					if ($Link['Player']->hasCustomShipName()) {
						echo $Link['Player']->getCustomShipName();
					} ?>
				</td>
				<td><?php echo $Link['Player']->getAllianceName(true); ?></td>
				<td class="shrink center">
					<a href="<?php echo $Link['RaceHREF']; ?>"><?php echo $Player->getColouredRaceName($Link['Player']->getRaceID()); ?></a>
				</td>
				<td class="shrink center"><?php echo $Link['Player']->getExperience(); ?></td><?php
				if ($Link['Player']->getAccount()->isNewbie()) { ?>
					<td width="10%" class="center dgreen">YES</td><?php
				} else { ?>
					<td width="10%" class="center red">NO</td><?php
				}
				if ($Link['Player']->getLastCPLAction() > TIME - 600) { ?>
					<td width="10%" class="center dgreen">YES</td><?php
				} else { ?>
					<td width="10%" class="center red">NO</td><?php
				}
				if (in_array($Player->getAccountID(), Globals::getHiddenPlayers())) { ?>
					<td class="center"><?php echo $Link['Player']->getSectorID(); ?></td><?php
				} ?>
				<td style="font-size:91%;" class="shrink center noWrap">
					<a href="<?php echo $Link['MessageHREF']; ?>">
						<span class="yellow">Send Message</span>
					</a>
					<br />
					<a href="<?php echo $Link['BountyHREF']; ?>">
						<span class="yellow">View Bounty</span>
					</a>
					<br />
					<a href="<?php echo $Link['HofHREF']; ?>">
						<span class="yellow">View Stats</span>
					</a>
					<br />
					<a href="<?php echo $Link['NewsHREF']; ?>">
						<span class="yellow">View News</span>
					</a><?php
					if (in_array($Player->getAccountID(), Globals::getHiddenPlayers())) { ?>
						<br />
						<a href="<?php echo $Link['JumpHREF']; ?>">
							<span class="yellow">Jump to Sector</span>
						</a><?php
					} ?>
				</td>
			</tr><?php
		} ?>
	</table><?php
}

if (isset($ResultPlayerLinks)) {
	DisplayResult(array($ResultPlayerLinks), $Player);
	echo "<br /><br />";
}
if (isset($SimilarPlayersLinks)) {
	DisplayResult($SimilarPlayersLinks, $Player);
}
