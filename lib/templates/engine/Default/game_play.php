<h1>Play Game</h1>

<a href="<?php echo $UserRankName ?>"><b style="color:yellow;">Rankings</b></a>
<br />You are ranked as <?php $this->doAn($UserRankName) ?> <span style="font-size:125%;color:greenyellow;"><?php echo $UserRankName ?></span> player.<p><br /><br />


<?php
if($Games['Play'])
{ ?>
	<table class="standard" cellspacing="0">
		<tr>
			<th align="center">&nbsp;</th>
			<th align="center">Game Name</th>
			<th align="center">Ship Condition</th>
			<th align="center">Playing</th>
			<th align="center">Last Active</th>
			<th align="center">Last Movement</th>
			<th align="center">End Date</th>
			<th align="center">Game Type</th>
		</tr><?php
		foreach($Games['Play'] as $Game)
		{ ?>
			<tr>
				<td>
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $Game['PlayGameLink'] ?>">&nbsp;Play Game&nbsp;</a>
					</div>
				</td>
				<td width="35%"><a href="<?php echo $Game['GameStatsLink'] ?>"><?php echo $Game['Name'] ?> (<?php echo $Game['ID'] ?>)</a></td>
				
				<td><?php echo $Game['Maintenance'] ?></td>
				<td><?php echo $Game['NumberPlaying'] ?></td>
				<td><?php echo $Game['LastActive'] ?></td>
				<td><?php echo $Game['LastMovement'] ?></td>
				<td><?php echo $Game['EndDate'] ?></td>
				<td><?php echo $Game['Type'] ?></td>
			</tr><?php
		} ?>
	</table><br />
	<br /><?php
} ?>
<h1>Join Game</h1><?php
if($Games['Join'])
{ ?>
	<table class="standard" cellspacing="0">
		<tr>
			<th align="center">&nbsp;</th>
			<th width="150">Game Name</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Max Players</th>
			<th>Type</th>
			<th>Game Speed</th>
			<th>Credits Needed</th>
		</tr>
		</tr><?php
		foreach($Games['Join'] as $Game)
		{ ?>
			<tr>
				<td>
					<div class="buttonA"><a class="buttonA" href="<?php echo $Game['JoinGameLink'] ?>">&nbsp;Join Game&nbsp;</a></div>
				</td>
				<td width="35%"><?php echo $Game['Name'] ?> (<?php echo $Game['ID'] ?>)</td>
				<td><?php echo $Game['StartDate'] ?></td>
				<td><?php echo $Game['EndDate'] ?></td>
				<td><?php echo $Game['MaxPlayers'] ?></td>
				<td><?php echo $Game['Type'] ?></td>
				<td><?php echo $Game['Speed'] ?></td>
				<td><?php echo $Game['Credits'] ?></td>
			</tr>
		<?php } ?>
	</table><br />
	<br /><?php
}
else
{
	?><p>You have joined all open games.</p><?php
} ?>
<br />
<br />
<h1>Previous Games</h1><?php
if($Games['Previous'])
{ ?>
	<table class="standard" cellspacing="0">
		<tr>
			<th width="150">Game Name</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Game Speed</th>
			<th colspan="3">Options</th>
		</tr>
		</tr><?php
		foreach($Games['Previous'] as $Game)
		{ ?>
			<tr>
				<td width="35%"><a href="<?php echo $Game['PreviousGameLink'] ?>"><?php echo $Game['Name'] ?> (<?php echo $Game['ID'] ?>)</a></td>
				<td><?php echo $Game['StartDate'] ?></td>
				<td><?php echo $Game['EndDate'] ?></td>
				<td><?php echo $Game['Speed'] ?></td>
				<td><a href="<?php echo $Game['PreviousGameHOFLink'] ?>">Hall Of Fame</a></td>
				<td><a href="<?php echo $Game['PreviousGameNewsLink'] ?>">Game News</a></td>
				<td><a href="<?php echo $Game['PreviousGameStatsLink'] ?>">Game Stats</a></td>
			</tr>
		<?php } ?>
	</table><br />
	<br /><?php
}
else
{
	?><p>There are no previous games.</p><?php
} ?>
<br />
<br />
<h1>Donate Money</h1>
<p>
	<a href="<?php echo $DonateLink ?>"><img src="images/donation.jpg" border="0" /></a>
</p>
<br />
<a href="<?php echo $OldAnnouncementsLink ?>"><h1>View Old Announcements</h1></a>
<br />
<br />

<?php
if($AdminPermissions)
{ ?>
	<h1>Admin Privileges</h1><br />
	<ul><?php
	foreach($AdminPermissions as $Permission)
	{ ?>
		<li><a href="<?php echo $Permission['PermissionLink'] ?>"><?php echo $Permission['Name'] ?></a></li><?php
	} ?>
	</ul><?php
} ?>