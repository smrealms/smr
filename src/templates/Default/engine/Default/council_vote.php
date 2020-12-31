<a href="<?php echo WIKI_URL; ?>/game-guide/politics" target="_blank"><img style="float: right;" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Politics"/></a>

<div class="center bold">Diplomatic Treaties</div><br />
<div class="center">
	Each council member is granted one vote per treaty.<br />
	Presidents have the right to veto (remove) the vote on any treaty.<br />
	Peace treaties must pass in both racial councils.
</div><br />

<?php
if (!$VoteTreaties) { ?>
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

	foreach ($VoteTreaties as $RaceID => $VoteInfo) { ?>
		<tr>
			<td><?php echo $ThisPlayer->getColouredRaceName($RaceID, true); ?></td>
			<td><?php echo $VoteInfo['Type']; ?></td>
			<td class="noWrap">
				<form method="POST" action="<?php echo $VoteInfo['HREF']; ?>">
					<input type="submit" name="action" value="Yes" <?php if ($VoteInfo['For']) { ?> style="background-color:green"<?php } ?> />
					&nbsp;
					<input type="submit" name="action" value="No" <?php if ($VoteInfo['Against']) { ?> style="background-color:green"<?php } ?> /><?php
					if ($ThisPlayer->isPresident()) { ?>
						&nbsp;
						<input type="submit" name="action" value="Veto" /><?php
					} ?>
				</form>
			</td>
			<td><?php echo $VoteInfo['YesVotes']; ?> / <?php echo $VoteInfo['NoVotes']; ?></td>
			<td class="noWrap"><?php echo date(DATE_FULL_SHORT_SPLIT, $VoteInfo['EndTime']); ?></td>
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

	foreach ($VoteRelations as $RaceID => $VoteInfo) { ?>
		<tr>
			<td>
				<a href="<?php echo Globals::getCouncilHREF($RaceID); ?>">
					<img src="<?php echo Globals::getRaceHeadImage($RaceID); ?>" width="60" height="64" /><br /><?php
					echo $ThisPlayer->getColouredRaceName($RaceID); ?>
				</a>
			</td>
			<td>
				<form method="POST" action="<?php echo $VoteInfo['HREF']; ?>">
					<input type="submit" name="action" value="Increase" <?php if ($VoteInfo['Increased']) { ?> style="background-color:green"<?php } ?> />
					&nbsp;
					<input type="submit" name="action" value="Decrease" <?php if ($VoteInfo['Decreased']) { ?> style="background-color:green"<?php } ?> />
				</form>
			</td>
			<td><?php echo get_colored_text($VoteInfo['Relations']); ?></td>
		</tr><?php
	} ?>
</table>
