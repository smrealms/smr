<?php declare(strict_types=1);

use Smr\Globals;
use Smr\Race;

/**
 * @var Smr\Player $ThisPlayer
 * @var array<int> $PeaceRaces
 * @var array<int> $NeutralRaces
 * @var array<int> $WarRaces
 */

?>
<a href="<?php echo WIKI_URL; ?>/game-guide/politics" target="_blank"><img style="float: right;" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Politics"/></a>
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
					foreach ($PeaceRaces as $RaceID) { ?>
						<tr>
							<td>
								<img src="<?php echo Race::getHeadImage($RaceID); ?>" width="100" height="106" /><br /><?php
								echo Globals::getColouredRaceNameForRace($RaceID, $ThisPlayer->getGameID(), $ThisPlayer->getRaceID()); ?>
							</td>
						</tr><?php
					} ?>
				</table>
			</td>
			<td>
				<table class="center"><?php
					foreach ($NeutralRaces as $RaceID) { ?>
						<tr>
							<td>
								<img src="<?php echo Race::getHeadImage($RaceID); ?>" width="100" height="106" /><br /><?php
								echo Globals::getColouredRaceNameForRace($RaceID, $ThisPlayer->getGameID(), $ThisPlayer->getRaceID()); ?>
							</td>
						</tr><?php
					} ?>
				</table>
			</td>
			<td>
				<table class="center"><?php
					foreach ($WarRaces as $RaceID) { ?>
						<tr>
							<td>
								<img src="<?php echo Race::getHeadImage($RaceID); ?>" width="100" height="106" /><br /><?php
								echo Globals::getColouredRaceNameForRace($RaceID, $ThisPlayer->getGameID(), $ThisPlayer->getRaceID()); ?>
							</td>
						</tr><?php
					} ?>
				</table>
			</td>
		</tr>
	</table>
</div>
