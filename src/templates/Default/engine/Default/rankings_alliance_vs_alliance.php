<?php declare(strict_types=1);

/**
 * @var string $SubmitHREF
 * @var array<array{ID: int, DetailsHREF: string, Name: string, Style: string}> $AllianceVs
 * @var string $DetailsName
 * @var array<int, array<int, array{Value: string|int, Style: string}>> $AllianceVsTable
 * @var array<array{Name: string, Kills: int}> $Kills
 * @var array<array{Name: string, Deaths: int}> $Deaths
 * @var array<int, Smr\Alliance> $ActiveAlliances
 */

?>
<div class="center">
<form method="POST" action="<?php echo $SubmitHREF; ?>">
	<p>Here are the rankings of alliances vs other alliances.<br />
	Click on an alliances name for more detailed stats.</p>

	<table class="standard center shrink">
		<tr>
			<td rowspan="2" colspan="2"></td>
			<th colspan="5">Deaths</th>
		</tr>

		<tr><?php
			foreach ($AllianceVs as $data) { ?>
				<td class="shrink">
					<a <?php echo $data['Style']; ?> href="<?php echo $data['DetailsHREF']; ?>"><?php echo $data['Name']; ?></a>
				</td><?php
			} ?>
		</tr>

		<tr>
			<th rowspan="6">Kills</th>
		</tr><?php
		foreach ($AllianceVs as $data) { ?>
			<tr>
				<td <?php echo $data['Style']; ?>>
					<select name="alliancer[]" style="width:155px"><?php
						foreach ($ActiveAlliances as $activeID => $curr_alliance) {
							$attr = ($data['ID'] === $activeID) ? 'selected' : ''; ?>
							<option value="<?php echo $activeID; ?>" <?php echo $attr; ?>><?php echo $curr_alliance->getAllianceDisplayName(); ?></option><?php
						} ?>
						<option value="0" <?php echo ($data['ID'] === 0) ? 'selected' : ''; ?>>No Alliance</option>
					</select>
				</td><?php
				foreach ($AllianceVs as $data2) {
					$dataTable = $AllianceVsTable[$data2['ID']][$data['ID']]; ?>
					<td <?php echo $dataTable['Style']; ?>><?php echo $dataTable['Value']; ?></td><?php
				} ?>
			</tr><?php
		} ?>
	</table>

	<br />
	<?php echo create_submit('action', 'Show'); ?>
</form>
</div>

<table class="center">
	<tr>
		<td width="45%" class="top"><?php
			if (count($Kills) > 0) { ?>
				<p>Kills for <?php echo $DetailsName; ?></p>
				<table class="standard center">
					<tr>
						<th>Alliance Name</th>
						<th>Amount</th>
					</tr><?php
					foreach ($Kills as $data) { ?>
						<tr>
							<td><?php echo $data['Name']; ?></td>
							<td><?php echo $data['Kills']; ?></td>
						</tr><?php
					} ?>
				</table><?php
			} else { ?>
				<p><?php echo $DetailsName ?> has no kills!</p><?php
			} ?>
		</td>

		<td width="10%">&nbsp;</td>
		<td width="45%" class="top"><?php
			if (count($Deaths) > 0) { ?>
				<p>Deaths for <?php echo $DetailsName; ?></p>
				<table class="standard center">
					<tr>
						<th>Alliance Name</th>
						<th>Amount</th>
					</tr><?php
					foreach ($Deaths as $data) { ?>
						<tr>
							<td><?php echo $data['Name']; ?></td>
							<td><?php echo $data['Deaths']; ?></td>
						</tr><?php
					} ?>
				</table><?php
			} else { ?>
				<p><?php echo $DetailsName; ?> has no deaths!</p><?php
			} ?>
		</td>
	</tr>
</table>
