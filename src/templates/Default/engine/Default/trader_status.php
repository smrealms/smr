<?php declare(strict_types=1);

use Smr\BountyType;
use Smr\Epoch;
use Smr\Globals;
use Smr\Race;

/**
 * @var Smr\Account $ThisAccount
 * @var Smr\Player $ThisPlayer
 * @var Smr\Ship $ThisShip
 * @var ?string $LeaveNewbieHREF
 * @var string $RelationsHREF
 * @var string $SavingsHREF
 * @var string $BountiesHREF
 * @var int $BountiesClaimable
 * @var string $HardwareHREF
 * @var array<string> $Hardware
 * @var Smr\PlayerLevel $NextLevel
 * @var string $UserRankingsHREF
 * @var string $NoteDeleteHREF
 * @var string $NoteAddHREF
 * @var array<int, string> $Notes
 */

?>
<table class="standard fullwidth">
	<tr>
		<td style="width:50%" class="top">
			<span class="yellow bold">Protection</span>
			<a href="<?php echo WIKI_URL; ?>/game-guide/protection" target="_blank">
				<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Protection"/>
			</a>
			<br />

			<?php
			if ($ThisPlayer->hasNewbieTurns()) { ?>
				You are under <span class="green">NEWBIE</span> protection.<br /><br />
				<div class="buttonA">
					<a class="buttonA" href="<?php echo $LeaveNewbieHREF; ?>">Leave Newbie Protection</a>
				</div>
			<?php
			} elseif ($ThisPlayer->hasFederalProtection()) { ?>
				You are under <span class="blue">FEDERAL</span> protection.<?php
			} else { ?>
				You are <span class="red">NOT</span> under protection.<?php
			} ?>

			<br /><br />

			<a href="<?php echo $RelationsHREF; ?>">
				<span class="yellow bold">Relations (Personal)</span>
			</a>

			<br /><?php
			foreach (Race::getAllNames() as $raceID => $raceName) {
				if ($ThisPlayer->getPersonalRelation($raceID) !== 0) {
					echo $raceName . ' : ' . get_colored_text($ThisPlayer->getPersonalRelation($raceID)) . '<br />';
				}
			} ?>

			<br />

			<a href="<?php echo Globals::getCouncilHREF($ThisPlayer->getRaceID()); ?>">
				<span class="yellow bold">Politics</span>
			</a>
			<a href="<?php echo WIKI_URL; ?>/game-guide/politics" target="_blank">
				<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Politics"/>
			</a>
			<br />

			<?php
			if ($ThisPlayer->isOnCouncil()) {
				if ($ThisPlayer->isPresident()) { ?>
					You are the <span class="red">President</span> of the ruling council.<?php
				} else { ?>
					You are a <span class="blue">member</span> of the ruling council.<?php
				}
			} else { ?>
				You are <span class="red">NOT</span> a member of the ruling council.<?php
			} ?>

			<br /><br />

			<a href="<?php echo $SavingsHREF; ?>">
				<span class="yellow bold">Savings</span>
			</a>
			<a href="<?php echo WIKI_URL; ?>/game-guide/locations#banks" target="_blank">
				<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Banks"/>
			</a>
			<br />
			You have <span class="yellow"><?php echo number_format($ThisPlayer->getBank()); ?></span> credits in your personal account.
			<?php
			if ($ThisPlayer->hasAlliance()) { ?>
				<br />
				Your alliance account contains <span class="yellow"><?php echo number_format($ThisPlayer->getAlliance()->getBank()); ?></span> credits.
			<?php
			} ?>

		</td>


		<td class="top" style="width:50%">

			<a href="<?php echo $BountiesHREF; ?>">
				<span class="yellow bold">Bounties</span>
			</a>
			<a href="<?php echo WIKI_URL; ?>/game-guide/locations#headquarters" target="_blank">
				<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Bounties"/>
			</a>

			<br />You can claim <span class="yellow"><?php echo $BountiesClaimable; ?></span> bounties.

			<?php
			if ($ThisPlayer->hasActiveBounty(BountyType::HQ)) { ?>
				<br />You are <span class="red">wanted</span> by the <span class="green">Federal Government</span>!<?php
			}
			if ($ThisPlayer->hasActiveBounty(BountyType::UG)) { ?>
				<br />You are <span class="red">wanted</span> by the <span class="red">Underground</span>!<?php
			} ?>

			<br /><br />
			<span class="yellow bold">Ship</span>
			<a href="ship_list.php" target="_blank">
				<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Ship List"/>
			</a>

			<br />Name: <?php echo $ThisShip->getName(); ?>
			<br />Speed: <?php echo $ThisShip->getRealSpeed(); ?> turns/hour
			<br />Max: <?php echo $ThisPlayer->getMaxTurns(); ?> turns
			<br />At max turns <span id="max_turns"><?php echo in_time_or_now($ThisPlayer->getTimeUntilMaxTurns(Epoch::time()), true); ?></span>.
			Next turn in <span id="next_turn"><?php echo format_time($ThisPlayer->getTimeUntilNextTurn(), true); ?></span>.
			<br /><br />

			<a href="<?php echo $HardwareHREF; ?>">
				<span class="yellow bold">Supported Hardware</span>
			</a>
			<a href="<?php echo WIKI_URL; ?>/game-guide/technologies" target="_blank">
				<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Technologies"/>
			</a>
			<br /><?php echo implode('<br />', $Hardware); ?><br /><br />

			<a href="level_requirements.php" target="levelRequirements">
				<span class="yellow bold">Next Level</span>
			</a>
			<a href="<?php echo WIKI_URL; ?>/game-guide/experience-levels" target="_blank">
				<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Experience Levels"/>
			</a>
			<br />
			<?php echo $NextLevel->name . ' : ' . number_format($NextLevel->expRequired); ?> experience

			<br /><br />
			<a href="<?php echo $UserRankingsHREF; ?>">
				<span class="yellow bold">User Ranking</span>
			</a>

			<br />You are ranked as a <span class="green"><?php echo $ThisAccount->getRank()->name; ?></span> player.<br /><br />
		</td>
	</tr>
</table>

<br />

<form class="standard" method="POST" action="<?php echo $NoteDeleteHREF; ?>">
	<table class="standard fullwidth">
		<tr>
			<th colspan="2">Notes</th>
		</tr>
		<?php
		foreach ($Notes as $NoteID => $Note) { ?>
			<tr>
				<td class="shrink">
					<input type="checkbox" name="note_id[]" value="<?php echo $NoteID; ?>" />
				</td>
				<td><?php echo bbify($Note); ?></td>
			</tr><?php
		} ?>
	</table>

	<br />
	<input type="submit" name="action" value="Delete Selected" />
</form>
<br />

<form class="standard" method="POST" action="<?php echo $NoteAddHREF; ?>">
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td>
				Enter text in the box below to create a new note.<br />
				(examples: trade routes, weapon locations, key alliance related locations)<br />
				<textarea spellcheck="true" name="note" required maxlength="1000"></textarea>
			</td>
		</tr>
	</table>
	<br />
	<input type="submit" name="action" value="Create New Note" />
	<small>&nbsp;&nbsp;&nbsp;Maximum note length is 1000 characters</small>
</form>
