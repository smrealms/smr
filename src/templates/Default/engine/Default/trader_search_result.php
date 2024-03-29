<?php declare(strict_types=1);

use Smr\Epoch;
use Smr\Player;

/**
 * @param array<array<string, mixed>> $Links
 */
function DisplayResult(array $Links, Player $Player): void { ?>
	<table class="standard" width="88%">
		<tr>
			<th>Name</th>
			<th>Alliance</th>
			<th>Race</th>
			<th>Experience</th>
			<th>Newbie</th>
			<th>Online</th><?php
			if ($Player->isObserver()) { ?>
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
				<td><?php echo $Link['Player']->getAllianceDisplayName(true); ?></td>
				<td class="shrink center">
					<?php echo $Player->getColouredRaceName($Link['Player']->getRaceID(), true); ?>
				</td>
				<td class="shrink center"><?php echo $Link['Player']->getExperience(); ?></td><?php
				if ($Link['Player']->hasNewbieStatus()) { ?>
					<td width="10%" class="center dgreen">YES</td><?php
				} else { ?>
					<td width="10%" class="center red">NO</td><?php
				}
				if ($Link['Player']->getLastCPLAction() > Epoch::time() - 600) { ?>
					<td width="10%" class="center dgreen">YES</td><?php
				} else { ?>
					<td width="10%" class="center red">NO</td><?php
				}
				if ($Player->isObserver()) { ?>
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
					if ($Player->isObserver()) { ?>
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

/**
 * @var Smr\Player $ThisPlayer
 */

if (isset($ResultPlayerLinks)) {
	DisplayResult([$ResultPlayerLinks], $ThisPlayer);
	echo '<br /><br />';
}
if (isset($SimilarPlayersLinks)) {
	DisplayResult($SimilarPlayersLinks, $ThisPlayer);
}
