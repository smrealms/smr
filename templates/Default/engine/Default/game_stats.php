<h2>Description</h2>
<?php echo $StatsGame->getDescription(); ?>
<br /><br />

<div align="center">
	<table class="standard">
		<tr>
			<td align="center">General Info</td>
			<td align="center">Other Info</td>
		</tr>
		<tr>
			<td valign="top" align="center">
				<table class="nobord">
					<tr>
						<td align="right">Name</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo $StatsGame->getName(); ?></td>
					</tr>
					<tr>
						<td align="right">Start Date</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo date(DATE_FULL_SHORT, $StatsGame->getStartDate()); ?></td>
					</tr>
					<tr>
						<td align="right">Start Turns Date</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo date(DATE_FULL_SHORT, $StatsGame->getStartTurnsDate()); ?></td>
					</tr>
					<tr>
						<td align="right">End Date</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo date(DATE_FULL_SHORT, $StatsGame->getEndDate()); ?></td>
					</tr>
					<tr>
						<td align="right">Max Turns</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format($StatsGame->getMaxTurns()); ?></td>
					</tr>
					<tr>
						<td align="right">Start Turn Hours</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format($StatsGame->getStartTurnHours()); ?></td>
					</tr>
					<tr>
						<td align="right">Max Players</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format($StatsGame->getMaxPlayers()); ?></td>
					</tr>
					<tr>
						<td align="right">Alliance Max Players</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format($StatsGame->getAllianceMaxPlayers()); ?></td>
					</tr>
					<tr>
						<td align="right">Alliance Max Vets</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format($StatsGame->getAllianceMaxVets()); ?></td>
					</tr>
					<tr>
						<td align="right">Game Type</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo $StatsGame->getGameType(); ?></td>
					</tr>
					<tr>
						<td align="right">Game Speed</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo $StatsGame->getGameSpeed(); ?></td>
					</tr>
					<tr>
						<td align="right">Credits Needed</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format($StatsGame->getCreditsNeeded()); ?></td>
					</tr>
					<tr>
						<td align="right">Stats Ignored</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo $StatsGame->isIgnoreStats()?'Yes':'No'; ?></td>
					</tr>
					<tr>
						<td align="right">Starting Credits</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format($StatsGame->getStartingCredits()); ?></td>
					</tr>
				</table>
			</td>
			<td valign="top" align="center">
				<table class="nobord">
					<tr>
						<td align="right">Total Players</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($TotalPlayers); ?></td>
					</tr>
					<tr>
						<td align="right">Alliances</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($TotalAlliances); ?></td>
					</tr>
					<tr>
						<td align="right">Highest Experience</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($HighestExp); ?></td>
					</tr>
					<tr>
						<td align="right">Highest Alignment</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($HighestAlign); ?></td>
					</tr>
					<tr>
						<td align="right">Lowest Alignment</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($LowestAlign); ?></td>
					</tr>
					<tr>
						<td align="right">Highest Kills</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($HighestKills); ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br />

	<table class="standard">
		<tr>
			<td align="center">Top 10 Players in Experience</td>
			<td align="center">Top 10 Players in Kills</td>
		</tr>
		<tr>
			<td class="center" style="border:none"><?php
				if(isset($ExperienceRankings)) { ?>
					<table class="nobord">
						<tr>
							<th align="center">Rank</th>
							<th align="center">Player</th>
							<th align="center">Experience</th>
						</tr><?php
						foreach($ExperienceRankings as $Rank => &$RankedPlayer) { ?>
							<tr>
								<td align="center"><?php echo $Rank; ?></td>
								<td align="center"><?php echo $RankedPlayer->getPlayerName(); ?></td>
								<td align="center"><?php echo number_format($RankedPlayer->getExperience()); ?></td>
							</tr><?php
						} unset($RankedPlayer); ?>
					</table><?php
				} ?>
			</td>
			<td align="center"><?php
				if(isset($KillRankings)) { ?>
					<table class="nobord">
						<tr>
							<th align="center">Rank</th>
							<th align="center">Player</th>
							<th align="center">Kills</th>
						</tr><?php
						foreach($KillRankings as $Rank => &$RankedPlayer) { ?>
							<tr>
								<td align="center"><?php echo $Rank; ?></td>
								<td align="center"><?php echo $RankedPlayer->getPlayerName(); ?></td>
								<td align="center"><?php echo number_format($RankedPlayer->getKills()); ?></td>
							</tr><?php
						} unset($RankedPlayer); ?>
					</table><?php
				} ?>
			</td>
		</tr>
	</table>
	<br />

	<table class="standard">
		<tr>
			<td align="center">Top 10 Alliances in Experience</td>
			<td align="center">Top 10 Alliances in Kills</td>
		</tr>
		<tr>
			<td class="center" style="border:none"><?php
				if (isset($AllianceExpRankings)) { ?>
					<table class="nobord">
						<tr>
							<th align="center">Rank</th>
							<th align="center">Alliance</th>
							<th align="center">Experience</th>
						</tr><?php
						foreach ($AllianceExpRankings as $Rank => $RankedAlliance) { ?>
							<tr>
								<td align="center"><?php echo $Rank; ?></td>
								<td align="center"><?php echo $RankedAlliance['Alliance']->getAllianceName(); ?></td>
								<td align="center"><?php echo number_format($RankedAlliance['Amount']); ?></td>
							</tr><?php
						} ?>
					</table><?php
				} ?>
			</td>
			<td align="center"><?php
				if (isset($AllianceKillRankings)) { ?>
					<table class="nobord">
						<tr>
							<th align="center">Rank</th>
							<th align="center">Alliance</th>
							<th align="center">Kills</th>
						</tr><?php
						foreach ($AllianceKillRankings as $Rank => $RankedAlliance) { ?>
							<tr>
								<td align="center"><?php echo $Rank; ?></td>
								<td align="center"><?php echo $RankedAlliance['Alliance']->getAllianceName(); ?></td>
								<td align="center"><?php echo number_format($RankedAlliance['Amount']); ?></td>
							</tr><?php
						} ?>
					</table><?php
				} ?>
			</td>
		</tr>
	</table>
</div>
