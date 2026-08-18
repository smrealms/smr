<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Game;

class GameStatsRenderer {

	/**
	 * @param array<int, array{Alliance: \Smr\Alliance, Class: string, Value: int, AllianceName: string}> $AllianceExpRankings
	 * @param array<int, array{Alliance: \Smr\Alliance, Class: string, Value: int, AllianceName: string}> $AllianceKillRankings
	 * @param array<int, array{Class: string, Player: \Smr\Player, Value: int}> $ExperienceRankings
	 * @param array<int, array{Class: string, Player: \Smr\Player, Value: int}> $KillRankings
	 * @param ?array<string, string> $PlayerInfo
	 */
	public static function render(
		Game $StatsGame,
		int $TotalPlayers,
		int $HighestExp,
		int $HighestAlign,
		int $LowestAlign,
		int $HighestKills,
		int $TotalAlliances,
		array $ExperienceRankings,
		array $KillRankings,
		array $AllianceExpRankings,
		array $AllianceKillRankings,
		?array $PlayerInfo,
		string $BackHref,
		Account $ThisAccount,
	): void {
		?>
		<a href="<?php echo $BackHref; ?>">&lt;&lt;Back</a>
		<br /><br />
		<h2>Description</h2>
		<?php echo bbify($StatsGame->getDescription(), $StatsGame->getGameID()); ?>
		<br /><br />
		<div>
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

		<?php
		if ($PlayerInfo !== null) { ?>
			<br />
			<h2>Your Trader</h2>
			<br />
			<table class="center standard">
				<tr><?php
					foreach (array_keys($PlayerInfo) as $colName) { ?>
						<th><?php echo $colName; ?></th><?php
					} ?>
				</tr>
				<tr><?php
					foreach (array_values($PlayerInfo) as $colValue) { ?>
						<td><?php echo $colValue; ?></td><?php
					} ?>
				</tr>
			</table>
			<br /><?php
		} ?>


		<h2>Rankings</h2>
		<div>
			<table class="center standard">
				<tr>
					<td>Top 10 Players in Experience</td>
					<td>Top 10 Players in Kills</td>
				</tr>
				<tr>
					<td><?php
						if (count($ExperienceRankings) > 0) { ?>
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
						if (count($KillRankings) > 0) { ?>
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

		<?php
	}

}
