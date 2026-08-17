<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\Player;
use Smr\Race;

class EmbassyRenderer {

	/**
	 * @param array<int, \Smr\Pages\Player\Council\EmbassyProcessor> $VoteRacePages
	 */
	public static function render(array $VoteRacePages, Player $ThisPlayer): void {
		?>
		<a href="<?php echo WIKI_URL; ?>/game-guide/politics" target="_blank"><img style="float: right;" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Politics"/></a>
		<div class="center bold">Diplomatic Treaties</div><br />
		<div class="center standard">
			Welcome President <?php echo $ThisPlayer->getDisplayName(); ?>,<br /><br />
			Below you may decide to declare War or make Peace with other races within the Universe.<br />Remember that Peace votes are subject to veto by corresponding Racial President.<br />Choose wisely, for the fate of your race may lie with your decision.
		</div><br /><br />

		<table class="standard center" width="50%">
			<tr>
				<th>Race</th>
				<th>Treaty</th>
			</tr><?php

			foreach ($VoteRacePages as $RaceID => $VoteRacePage) { ?>
				<tr>
					<td><img src="<?php echo Race::getHeadImage($RaceID); ?>" width="60" height="64" /><br /><?php echo $ThisPlayer->getColouredRaceName($RaceID, true); ?></td>
					<td>
						<form method="POST" action="<?php echo $VoteRacePage->href(); ?>">
							<?php echo $VoteRacePage->actionPeace->html('Peace'); ?>
							&nbsp;
							<?php echo $VoteRacePage->actionWar->html('War'); ?>
						</form>
					</td>
				</tr><?php
			} ?>
		</table>

		<?php
	}

}
