<?php declare(strict_types=1);

/**
 * @var string $GameName
 * @var string $Start
 * @var string $End
 * @var string $Type
 * @var float $Speed
 * @var int $NumAlliances
 * @var int $NumPlayers
 * @var int $MaxExp
 * @var int $MaxAlign
 * @var int $MinAlign
 * @var int $MaxKills
 * @var array<array{bold: string, exp: int, name: string}> $PlayerExp
 * @var array<array{bold: string, kills: int, name: string}> $PlayerKills
 * @var array<array{bold: string, exp: int, link: string}> $AllianceExp
 * @var array<array{bold: string, kills: int, link: string}> $AllianceKills
 */

?>
<table class="center">
	<tr>
		<td class="top center">
			<table class="standard left">
				<tr>
					<th colspan="2">General Info</th>
				</tr>
				<tr>
					<td>Name</td>
					<td><?php echo $GameName; ?></td>
				</tr>
				<tr>
					<td>Start Date</td>
					<td><?php echo $Start; ?></td>
				</tr>
				<tr>
					<td>End Date</td>
					<td><?php echo $End; ?></td>
				</tr>
				<tr>
					<td>Game Type</td>
					<td><?php echo $Type; ?></td>
				</tr>
				<tr>
					<td>Game Speed</td>
					<td><?php echo $Speed; ?></td>
				</tr>
			</table>
		</td>

		<td class="top center">
			<table class="standard left">
				<tr>
					<th colspan="2">Other Info</th>
				</tr>
				<tr>
					<td>Players</td>
					<td><?php echo number_format($NumPlayers); ?></td>
				</tr>
				<tr>
					<td>Alliances</td>
					<td><?php echo number_format($NumAlliances); ?></td>
				</tr>
				<tr>
					<td>Highest Experience</td>
					<td><?php echo number_format($MaxExp); ?></td>
				</tr>
				<tr>
					<td>Highest Alignment</td>
					<td><?php echo number_format($MaxAlign); ?></td>
				</tr>
				<tr>
					<td>Lowest Alignment</td>
					<td><?php echo number_format($MinAlign); ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br />

<table class="center">
	<tr>
		<td>Top 10 Players in Experience</td>
		<td>Top 10 Players in Kills</td>
	</tr>
	<tr>
		<td class="top">
			<table class="standard">
				<tr>
					<th>Rank</th>
					<th>Player</th>
					<th>Experience</th>
				</tr><?php
				foreach ($PlayerExp as $index => $player) { ?>
					<tr <?php echo $player['bold']; ?>>
						<td><?php echo $index + 1; ?></td>
						<td><?php echo htmlentities($player['name']); ?></td>
						<td><?php echo number_format($player['exp']); ?></td>
					</tr><?php
				} ?>
			</table>
		</td>

		<td class="top">
			<table class="standard">
				<tr>
					<th>Rank</th>
					<th>Player</th>
					<th>Kills</th>
				</tr><?php
				foreach ($PlayerKills as $index => $player) { ?>
					<tr <?php echo $player['bold']; ?>>
						<td><?php echo $index + 1; ?></td>
						<td><?php echo htmlentities($player['name']); ?></td>
						<td><?php echo number_format($player['kills']); ?></td>
					</tr><?php
				} ?>
			</table>
		</td>
	</tr>
</table>
<br />

<table class="center">
	<tr>
		<td>Top 10 Alliances in Experience</td>
		<td>Top 10 Alliances in Kills</td>
	</tr>
	<tr>
		<td class="top">
			<table class="standard">
				<tr>
					<th>Rank</th>
					<th>Alliance</th>
					<th>Experience</th>
				</tr><?php
				foreach ($AllianceExp as $index => $alliance) { ?>
					<tr <?php echo $alliance['bold']; ?>>
						<td><?php echo $index + 1; ?></td>
						<td><?php echo $alliance['link']; ?></td>
						<td><?php echo number_format($alliance['exp']); ?></td>
					</tr><?php
				} ?>
			</table>
		</td>

		<td class="top">
			<table class="standard">
				<tr>
					<th>Rank</th>
					<th>Alliance</th>
					<th>Kills</th>
				</tr><?php
				foreach ($AllianceKills as $index => $alliance) { ?>
					<tr <?php echo $alliance['bold']; ?>>
						<td><?php echo $index + 1; ?></td>
						<td><?php echo $alliance['link']; ?></td>
						<td><?php echo number_format($alliance['kills']); ?></td>
					</tr><?php
				} ?>
			</table>
		</td>
	</tr>
</table>
<br />
