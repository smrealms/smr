<table class="standard" align="center" width="75%">
	<tr>
		<th>Race</th>
		<th>Vote</th>
		<th>Our Relation<br />with them</th>
		<th>Their Relation<br />with us</th>
	</tr><?php

	foreach($VoteRelations as $RaceID => $VoteInfo)
	{ ?>
		<tr>
			<td align="center"><?php echo $ThisPlayer->getColouredRaceName($RaceID); ?></td>
			<td align="center">
				<form method="POST" action="<?php echo $VoteInfo['HREF']; ?>">
					<input type="submit" name="action" value="Increase" id="InputFields"<?php if($VoteInfo['Increased']){ ?> style="background-color:green"<?php } ?> />
					&nbsp;
					<input type="submit" name="action" value="Decrease" id="InputFields"<?php if($VoteInfo['Decreased']){ ?> style="background-color:green"<?php } ?> />
				</form>
			</td>
			<td align="center"><?php echo $VoteInfo['RelationToThem']; ?></td>
			<td align="center"><?php echo $VoteInfo['RelationToUs']; ?></td>
		</tr><?php
	} ?>
</table>

<p>&nbsp;</p><?php

if ($VoteTreaties)
{ ?>
	<table class="standard" align="center" width="65%">
		<tr>
			<th>Race</th>
			<th>Treaty</th>
			<th>Option</th>
			<th>Currently</th>
			<th>End Time</th>
		</tr><?php

	foreach($VoteTreaties as $RaceID => $VoteInfo)
	{ ?>
		<tr>
			<td align="center"><?php echo $ThisPlayer->getColouredRaceName($RaceID); ?></td>
			<td align="center"><?php echo $VoteInfo['Type']; ?></td>
			<td class="noWrap" align="center">
				<form method="POST" action="<?php echo $VoteInfo['HREF']; ?>">
					<input type="submit" name="action" value="Yes" id="InputFields"<?php if($VoteInfo['For']){ ?> style="background-color:green"<?php } ?> />
					&nbsp;
					<input type="submit" name="action" value="No" id="InputFields"<?php if($VoteInfo['Against']){ ?> style="background-color:green"<?php } ?> /><?php
					if ($ThisPlayer->isPresident())
					{ ?>
						&nbsp;
						<input type="submit" name="action" value="Veto" id="InputFields" /><?php
					} ?>
				</form>
			</td>
			<td align="center"><?php echo $VoteInfo['YesVotes']; ?> / <?php echo $VoteInfo['NoVotes']; ?></td>
			<td class="noWrap" align="center"><?php echo date(DATE_FULL_SHORT_SPLIT, $VoteInfo['EndTime']); ?></td>
		</tr><?php
	} ?>
	</table><?php
}
?>