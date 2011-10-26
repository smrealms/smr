<?php
if(isset($ErrorMessage))
{
	echo $ErrorMessage; ?><br /><br /><?php
}
if(isset($Message))
{
	echo $Message; ?><br /><br /><?php
} ?>

<a href="<?php echo $ThisAccount->getUserRankingHREF(); ?>"><b class="yellow">Rankings</b></a>
<br />You are ranked as <?php $this->doAn($ThisAccount->getRankName()); ?> <span style="font-size:125%;color:greenyellow;"><?php echo $UserRankName ?></span> player.<br /><br />

<?php
if(isset($Games['Play']))
{ ?>
	<table class="standard">
		<tr>
			<th align="center">&nbsp;</th>
			<th align="center">Game Name</th>
			<th align="center">Turns</th>
			<th align="center">Playing</th>
			<th align="center">Last Active</th>
			<th align="center">Last Movement</th>
			<th align="center">End Date</th>
			<th align="center">Game Type</th>
			<th align="center">Game Speed</th>
		</tr><?php
		foreach($Games['Play'] as $Game)
		{ ?>
			<tr>
				<td>
					<div class="buttonA">
						<a id="game_play_<?php echo $Game['ID']; ?>" class="buttonA" href="<?php echo $Game['PlayGameLink']; ?>">&nbsp;Play Game&nbsp;</a>
					</div>
				</td>
				<td width="35%"><a href="<?php echo $Game['GameStatsLink']; ?>"><?php echo $Game['Name']; ?> (<?php echo $Game['ID']; ?>)</a></td>
				
				<td><?php echo $Game['Maintenance']; ?></td>
				<td><?php echo $Game['NumberPlaying']; ?></td>
				<td><?php echo $Game['LastActive']; ?></td>
				<td><?php echo $Game['LastMovement']; ?></td>
				<td class="noWrap"><?php echo $Game['EndDate']; ?></td>
				<td><?php echo $Game['Type']; ?></td>
				<td><?php echo $Game['Speed']; ?></td>
			</tr><?php
		} ?>
	</table><br />
	<br /><?php
}
?><h1><a href="<?php echo $VotingHref; ?>">Voting</a></h1><?php
if (isset($Voting))
{
	?>Please take a couple of seconds to answer the following question(s) for the SMR Admin team. Thanks!<?php
	foreach($Voting as $Vote)
	{
		?><br /><br />
		<form name="FORM" method="POST" action="<?php echo $Vote['HREF'] ?>">
			<span class="bold"><?php echo bbifyMessage($Vote['Question']); ?></span> (<?php echo $Vote['TimeRemaining']; ?> Remaining)<br /><?php
			foreach($Vote['Options'] as $VoteOption)
			{ ?>
				<input type="radio" name="vote" value="<?php echo $VoteOption['ID']; ?>"<?php if($VoteOption['Chosen']) { ?> checked<?php } ?>><?php echo bbifyMessage($VoteOption['Text']); ?> (<?php echo $VoteOption['Votes']; ?> votes)<br /><?php
			} ?>
			<input type="submit" name="submit" value="Vote!"><br /><br />
		</form><?php
	}
} ?><br />
<h1>Join Game</h1><?php
if(isset($Games['Join']))
{ ?>
	<table class="standard">
		<tr>
			<th align="center">&nbsp;</th>
			<th width="150">Game Name</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Max Players</th>
			<th>Type</th>
			<th>Game Speed</th>
			<th>Credits Needed</th>
		</tr><?php
		foreach($Games['Join'] as $Game)
		{ ?>
			<tr>
				<td>
					<div class="buttonA"><a id="game_join_<?php echo $Game['ID']; ?>" class="buttonA" href="<?php echo $Game['JoinGameLink']; ?>">&nbsp;<?php if(TIME < $Game['StartDate']) {?>View Info<?php }else{ ?>Join Game<?php } ?>&nbsp;</a></div>
				</td>
				<td width="35%"><?php echo $Game['Name']; ?> (<?php echo $Game['ID']; ?>)</td>
				<td class="noWrap"><?php echo $Game['StartDate']; ?></td>
				<td class="noWrap"><?php echo $Game['EndDate']; ?></td>
				<td><?php echo $Game['MaxPlayers']; ?></td>
				<td><?php echo $Game['Type']; ?></td>
				<td><?php echo $Game['Speed']; ?></td>
				<td><?php echo $Game['Credits']; ?></td>
			</tr>
		<?php } ?>
	</table><?php
}
else
{
	?><p>You have joined all open games.</p><?php
} ?>
<br />
<br />
<h1>Donate Money</h1>
<p>
	<a href="<?php echo $DonateLink; ?>"><img src="images/donation.png" border="0" alt="donate" /></a>
</p>
<br />
<h1><a href="<?php echo $OldAnnouncementsLink; ?>">View Old Announcements</a></h1>
<br />
<br />

<?php
if(isset($AdminPermissions))
{ ?>
	<h1>Admin Privileges</h1><br />
	<ul><?php
	foreach($AdminPermissions as $Permission)
	{ ?>
		<li><?php
			if($Permission['PermissionLink']!==false)
			{
				?><a href="<?php echo $Permission['PermissionLink']; ?>"><?php
			}
			echo $Permission['Name'];
			if($Permission['PermissionLink']!==false)
			{
				?></a><?php
			} ?>
		</li><?php
	} ?>
	</ul>
	<br />
	<br /><?php
} ?>
<h1>Previous Games</h1><?php
if(isset($Games['Previous']))
{ ?>
	<table class="standard">
		<tr>
			<th width="150">Game Name</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Game Speed</th>
			<th colspan="3">Options</th>
		</tr><?php
		foreach($Games['Previous'] as $Game)
		{ ?>
			<tr>
				<td width="35%"><?php if(isset($Game['PreviousGameLink'])){ ?><a href="<?php echo $Game['PreviousGameLink']; ?>"><?php } echo $Game['Name']; ?> (<?php echo $Game['ID']; ?>)<?php if(isset($Game['PreviousGameLink'])){ ?></a><?php } ?></td>
				<td><?php echo $Game['StartDate'] ?></td>
				<td><?php echo $Game['EndDate'] ?></td>
				<td><?php echo $Game['Speed'] ?></td>
				<td><a href="<?php echo $Game['PreviousGameHOFLink']; ?>">Hall Of Fame</a></td>
				<td><a href="<?php echo $Game['PreviousGameNewsLink']; ?>">Game News</a></td>
				<td><?php if(isset($Game['PreviousGameStatsLink'])){ ?><a href="<?php echo $Game['PreviousGameStatsLink']; ?>">Game Stats</a><?php } ?></td>
			</tr>
		<?php } ?>
	</table><?php
}
else
{
	?><p>There are no previous games.</p><?php
} ?>