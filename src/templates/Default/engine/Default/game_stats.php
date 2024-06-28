<?php declare(strict_types=1);

/**
 * @var Smr\Account $ThisAccount
 * @var Smr\Game $StatsGame
 * @var int $TotalPlayers
 * @var int $HighestExp
 * @var int $HighestAlign
 * @var int $LowestAlign
 * @var int $HighestKills
 * @var int $TotalAlliances
 * @var array<int, array{Alliance: Smr\Alliance, Class: string, Value: int, AllianceName: string}> $AllianceExpRankings
 * @var array<int, array{Alliance: Smr\Alliance, Class: string, Value: int, AllianceName: string}> $AllianceKillRankings
 */

?>
<h2>Description</h2>
<?php echo bbify($StatsGame->getDescription(), $StatsGame->getGameID()); ?>
<br /><br />

<div class="center">
	<table class="center standard">
		<tr>
			<td>General Info</td>
			<td>Other Info</td>
		</tr>
		<tr>
			<td valign="top" class="left">
				<table class="nobord">
					<tr>
						<td class="right">Name</td>
						<td>&nbsp;</td>
						<td><?php echo $StatsGame->getName(); ?></td>
					</tr>
					<tr>
						<td class="right">Start Date</td>
						<td>&nbsp;</td>
						<td><?php echo date($ThisAccount->getDateTimeFormat(), $StatsGame->getStartTime()); ?></td>
					</tr>
					<tr>
						<td class="right">End Date</td>
						<td>&nbsp;</td>
						<td><?php echo date($ThisAccount->getDateTimeFormat(), $StatsGame->getEndTime()); ?></td>
					</tr>
					<tr>
						<td class="right">Max Turns</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($StatsGame->getMaxTurns()); ?></td>
					</tr>
					<tr>
						<td class="right">Start Turn Hours</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($StatsGame->getStartTurnHours()); ?></td>
					</tr>
					<tr>
						<td class="right">Max Players</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($StatsGame->getMaxPlayers()); ?></td>
					</tr>
					<tr>
						<td class="right">Alliance Max Players</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($StatsGame->getAllianceMaxPlayers()); ?></td>
					</tr>
					<tr>
						<td class="right">Alliance Max Vets</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($StatsGame->getAllianceMaxVets()); ?></td>
					</tr>
					<tr>
						<td class="right">Game Type</td>
						<td>&nbsp;</td>
						<td><?php echo $StatsGame->getGameType(); ?></td>
					</tr>
					<tr>
						<td class="right">Game Speed</td>
						<td>&nbsp;</td>
						<td><?php echo $StatsGame->getGameSpeed(); ?></td>
					</tr>
					<tr>
						<td class="right">Credits Needed</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($StatsGame->getCreditsNeeded()); ?></td>
					</tr>
					<tr>
						<td class="right">Stats Ignored</td>
						<td>&nbsp;</td>
						<td><?php echo $StatsGame->isIgnoreStats() ? 'Yes' : 'No'; ?></td>
					</tr>
					<tr>
						<td class="right">Starting Credits</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($StatsGame->getStartingCredits()); ?></td>
					</tr>
					<tr>
						<td class="right">Port Destruction</td>
						<td>&nbsp;</td>
						<td><?php echo $StatsGame->canDestroyPorts() ? 'Yes' : 'No'; ?></td>
					</tr>
				</table>
			</td>
			<td valign="top" class="left">
				<table class="nobord">
					<tr>
						<td class="right">View warp chart</td>
						<td></td>
						<td>
							<a href="map_warps.php?game=<?php echo $StatsGame->getGameID(); ?>" target="_blank">
								<img src="images/warp_chart.svg" height="24" width="24" style="vertical-align: middle;" />
							</a>
						</td>
					<tr>
						<td class="right">Total Players</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($TotalPlayers); ?></td>
					</tr>
					<tr>
						<td class="right">Alliances</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($TotalAlliances); ?></td>
					</tr>
					<tr>
						<td class="right">Highest Experience</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($HighestExp); ?></td>
					</tr>
					<tr>
						<td class="right">Highest Alignment</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($HighestAlign); ?></td>
					</tr>
					<tr>
						<td class="right">Lowest Alignment</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($LowestAlign); ?></td>
					</tr>
					<tr>
						<td class="right">Highest Kills</td>
						<td>&nbsp;</td>
						<td><?php echo number_format($HighestKills); ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br />

	<table class="center standard">
		<tr>
			<td>Top 10 Players in Experience</td>
			<td>Top 10 Players in Kills</td>
		</tr>
		<tr>
			<td><?php
				if (isset($ExperienceRankings)) { ?>
					<table class="nobord">
						<tr>
							<th>Rank</th>
							<th>Player</th>
							<th>Experience</th>
						</tr><?php
						foreach ($ExperienceRankings as $Rank => $RankedPlayer) { ?>
							<tr <?php echo $RankedPlayer['Class']; ?>>
								<td><?php echo $Rank; ?></td>
								<td><?php echo $RankedPlayer['Player']->getDisplayName(); ?></td>
								<td><?php echo number_format($RankedPlayer['Value']); ?></td>
							</tr><?php
						} ?>
					</table><?php
				} ?>
			</td>
			<td><?php
				if (isset($KillRankings)) { ?>
					<table class="nobord">
						<tr>
							<th>Rank</th>
							<th>Player</th>
							<th>Kills</th>
						</tr><?php
						foreach ($KillRankings as $Rank => $RankedPlayer) { ?>
							<tr <?php echo $RankedPlayer['Class']; ?>>
								<td><?php echo $Rank; ?></td>
								<td><?php echo $RankedPlayer['Player']->getDisplayName(); ?></td>
								<td><?php echo number_format($RankedPlayer['Value']); ?></td>
							</tr><?php
						} ?>
					</table><?php
				} ?>
			</td>
		</tr>
	</table>
	<br />

	<table class="center standard">
		<tr>
			<td>Top 10 Alliances in Experience</td>
			<td>Top 10 Alliances in Kills</td>
		</tr>
		<tr>
			<td>
				<table class="nobord">
					<tr>
						<th>Rank</th>
						<th>Alliance</th>
						<th>Experience</th>
					</tr><?php
					foreach ($AllianceExpRankings as $Rank => $RankedAlliance) { ?>
						<tr <?php echo $RankedAlliance['Class']; ?>>
							<td><?php echo $Rank; ?></td>
							<td><?php echo $RankedAlliance['AllianceName']; ?></td>
							<td><?php echo number_format($RankedAlliance['Value']); ?></td>
						</tr><?php
					} ?>
				</table>
			</td>
			<td>
				<table class="nobord">
					<tr>
						<th>Rank</th>
						<th>Alliance</th>
						<th>Kills</th>
					</tr><?php
					foreach ($AllianceKillRankings as $Rank => $RankedAlliance) { ?>
						<tr <?php echo $RankedAlliance['Class']; ?>>
							<td><?php echo $Rank; ?></td>
							<td><?php echo $RankedAlliance['AllianceName']; ?></td>
							<td><?php echo number_format($RankedAlliance['Value']); ?></td>
						</tr><?php
					} ?>
				</table>
			</td>
		</tr>
	</table>
</div>
