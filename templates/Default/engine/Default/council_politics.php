<div class="center">
	<p>We currently have the following diplomatic relationships:</p>
	<table class="center">
		<tr>
			<th width="150">Peace</th>
			<th width="150">Neutral</th>
			<th width="150">War</th>
		</tr>
		<tr>
			<td>
				<table class="center"><?php
					foreach ($PeaceRaces as $RaceID => $raceInfo) { ?>
						<tr>
							<td>
								<img src="<?php echo Globals::getRaceHeadImage($RaceID); ?>" width="100" height="106" /><br /><?php
								echo Globals::getColouredRaceNameForRace($RaceID, $ThisPlayer->getGameID(), $ThisPlayer->getRaceID()); ?>
							</td>
						</tr><?php
					} ?>
				</table>
			</td>
			<td>
				<table class="center"><?php
					foreach ($NeutralRaces as $RaceID => $raceInfo) { ?>
						<tr>
							<td>
								<img src="<?php echo Globals::getRaceHeadImage($RaceID); ?>" width="100" height="106" /><br /><?php
								echo Globals::getColouredRaceNameForRace($RaceID, $ThisPlayer->getGameID(), $ThisPlayer->getRaceID()); ?>
							</td>
						</tr><?php
					} ?>
				</table>
			</td>
			<td>
				<table class="center"><?php
					foreach ($WarRaces as $RaceID => $raceInfo) { ?>
						<tr>
							<td>
								<img src="<?php echo Globals::getRaceHeadImage($RaceID); ?>" width="100" height="106" /><br /><?php
								echo Globals::getColouredRaceNameForRace($RaceID, $ThisPlayer->getGameID(), $ThisPlayer->getRaceID()); ?>
							</td>
						</tr><?php
					} ?>
				</table>
			</td>
		</tr>
	</table>
</div>