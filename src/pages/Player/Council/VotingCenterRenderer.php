<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\Account;
use Smr\Globals;
use Smr\Player;
use Smr\Race;

class VotingCenterRenderer {

	/**
	 * @param array<int, array{VotePage: \Smr\Pages\Player\Council\VotingCenterProcessor, Increased: bool, Decreased: bool, Relations: int}> $VoteRelations
	 * @param array<int, array{VotePage: \Smr\Pages\Player\Council\VotingCenterProcessor, VetoPage: ?\Smr\Page\Page, Type: string, EndTime: int, For: bool, Against: bool, NoVotes: int, YesVotes: int}> $VoteTreaties
	 */
	public static function render(array $VoteRelations, array $VoteTreaties, Account $ThisAccount, Player $ThisPlayer): void {
		?>
		<a href="<?php echo WIKI_URL; ?>/game-guide/politics" target="_blank"><img style="float: right;" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Politics"/></a>

		<div class="center bold">Diplomatic Treaties</div><br />
		<div class="center">
			Each council member is granted one vote per treaty.<br />
			Presidents have the right to veto (remove) the vote on any treaty.<br />
			Peace treaties must pass in both racial councils.
		</div><br />

		<?php
		if (count($VoteTreaties) === 0) { ?>
			<div class="center"><i>There are no treaties to vote on at this time.</i></div>
		<?php
		} else { ?>
			<table class="standard center" width="65%">
				<tr>
					<th>Race</th>
					<th>Treaty</th>
					<th>Option</th>
					<th>Currently</th>
					<th>End Time</th>
				</tr><?php

			foreach ($VoteTreaties as $RaceID => $VoteInfo) {
				$VotePage = $VoteInfo['VotePage']; ?>
				<tr>
					<td><?php echo $ThisPlayer->getColouredRaceName($RaceID, true); ?></td>
					<td><?php echo $VoteInfo['Type']; ?></td>
					<td class="noWrap">
						<form method="POST" action="<?php echo $VotePage->href(); ?>">
							<?php echo $VotePage->actionTreatyYes->html('Yes', ($VoteInfo['For'] ? ['style' => 'background-color:green'] : [])); ?>
							&nbsp;
							<?php echo $VotePage->actionTreatyNo->html('No', ($VoteInfo['Against'] ? ['style' => 'background-color:green'] : []));
							if ($VoteInfo['VetoPage'] !== null) { ?>
								&nbsp;
								<?php echo create_submit_link($VoteInfo['VetoPage'], 'Veto');
							} ?>
						</form>
					</td>
					<td><?php echo $VoteInfo['YesVotes']; ?> / <?php echo $VoteInfo['NoVotes']; ?></td>
					<td class="noWrap"><?php echo date($ThisAccount->getDateTimeFormatSplit(), $VoteInfo['EndTime']); ?></td>
				</tr><?php
			} ?>
			</table><?php
		}
		?>

		<p>&nbsp;</p>

		<div class="center bold">Diplomatic Relations</div><br />
		<div class="center standard">
			Each council member is entitled to one vote daily.<br />
			Each vote counts for +/-<?php echo RELATIONS_VOTE_CHANGE; ?> with that race.<br />
			Results are updated at 00:00 daily.
		</div><br />

		<table class="standard center" width="75%">
			<tr>
				<th>Race</th>
				<th>Vote</th>
				<th>Relations</th>
			</tr><?php

			foreach ($VoteRelations as $RaceID => $VoteInfo) {
				$VotePage = $VoteInfo['VotePage']; ?>
				<tr>
					<td>
						<a href="<?php echo Globals::getCouncilHREF($RaceID); ?>">
							<img src="<?php echo Race::getHeadImage($RaceID); ?>" width="60" height="64" /><br /><?php
							echo $ThisPlayer->getColouredRaceName($RaceID); ?>
						</a>
					</td>
					<td>
						<form method="POST" action="<?php echo $VotePage->href(); ?>">
							<?php echo $VotePage->actionRelationsIncrease->html('Increase', ($VoteInfo['Increased'] ? ['style' => 'background-color:green'] : [])); ?>
							&nbsp;
							<?php echo $VotePage->actionRelationsDecrease->html('Decrease', ($VoteInfo['Decreased'] ? ['style' => 'background-color:green'] : [])); ?>
						</form>
					</td>
					<td><?php echo get_colored_text($VoteInfo['Relations']); ?></td>
				</tr><?php
			} ?>
		</table>

		<?php
	}

}
