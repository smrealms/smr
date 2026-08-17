<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\Globals;
use Smr\Player;
use Smr\Race;

class PoliticalStatusRenderer {

/**
 * @param array<int> $PeaceRaces
 * @param array<int> $NeutralRaces
 * @param array<int> $WarRaces
 */
public static function render(array $PeaceRaces, array $NeutralRaces, array $WarRaces, Player $ThisPlayer): void {
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

<?php
}

}
