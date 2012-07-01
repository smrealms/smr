<div align="center">
	<p>We are at War/Peace<br />with the following races:</p>
	<table>
		<tr>
			<th width="150">Peace</th>
			<th width="150">Neutral</th>
			<th width="150">War</th>
		</tr>
		<tr>
			<td align="center" valign="top">
				<table><?php
					foreach ($PeaceRaces as $RaceID => $raceInfo) { ?>
						<tr>
							<td align="center"><?php echo Globals::getColouredRaceNameForRace($RaceID, $ThisPlayer->getGameID(), $ThisPlayer->getRaceID()); ?></td>
						</tr><?php
					} ?>
				</table>
			</td>
			<td align="center" valign="top">
				<table><?php
					foreach ($NeutralRaces as $RaceID => $raceInfo) { ?>
						<tr>
							<td align="center"><?php echo Globals::getColouredRaceNameForRace($RaceID, $ThisPlayer->getGameID(), $ThisPlayer->getRaceID()); ?></td>
						</tr><?php
					} ?>
				</table>
			</td>
			<td align="center" valign="top">
				<table><?php
					foreach ($WarRaces as $RaceID => $raceInfo) { ?>
						<tr>
							<td align="center"><?php echo Globals::getColouredRaceNameForRace($RaceID, $ThisPlayer->getGameID(), $ThisPlayer->getRaceID()); ?></td>
						</tr><?php
					} ?>
				</table>
			</td>
		</tr>
	</table>
</div>