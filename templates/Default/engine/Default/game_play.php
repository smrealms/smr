<?php
if (isset($ErrorMessage)) {
	echo $ErrorMessage; ?><br /><br /><?php
}
if (isset($Message)) {
	echo $Message; ?><br /><br /><?php
} ?>

<a href="<?php echo $ThisAccount->getUserRankingHREF(); ?>"><b class="yellow">Rankings</b></a>
<br />You are ranked as <?php $this->doAn($ThisAccount->getRankName()); ?> <span style="font-size:125%;color:greenyellow;"><?php echo $UserRankName ?></span> player.<br /><br />

<div id="playGames" class="ajax"><?php
	if (isset($Games['Play'])) { ?>
		<table class="standard">
			<tr>
				<th>&nbsp;</th>
				<th>Game Name</th>
				<th>Turns</th>
				<th>Playing</th>
				<th>Last Movement</th>
				<th>End Date</th>
				<th>Game Type</th>
				<th>Game Speed</th>
			</tr><?php
			foreach ($Games['Play'] as $Game) { ?>
				<tr>
					<td>
						<div class="buttonA">
							<a id="game_play_<?php echo $Game['ID']; ?>" class="buttonA" href="<?php echo $Game['PlayGameLink']; ?>">Play Game</a>
						</div>
					</td>
					<td width="35%"><a href="<?php echo $Game['GameStatsLink']; ?>"><?php echo $Game['Name']; ?> (<?php echo $Game['ID']; ?>)</a></td>

					<td class="center"><?php echo $Game['Turns']; ?></td>
					<td class="center"><?php echo $Game['NumberPlaying']; ?></td>
					<td><?php echo $Game['LastMovement']; ?></td>
					<td class="noWrap"><?php echo $Game['EndDate']; ?></td>
					<td class="center"><?php echo $Game['Type']; ?></td>
					<td class="center"><?php echo $Game['Speed']; ?></td>
				</tr><?php
			} ?>
		</table><br />
		<br /><?php
	} ?>
</div>

<h1>Join Game</h1>
<div id="joinGames" class="ajax"><?php
	if (isset($Games['Join'])) { ?>
		<table class="standard">
			<tr>
				<th>&nbsp;</th>
				<th width="150">Game Name</th>
				<th>Start Date</th>
				<th>End Date</th>
				<th>Players</th>
				<th>Type</th>
				<th>Game Speed</th>
				<th>Credits Needed</th>
			</tr><?php
			foreach ($Games['Join'] as $Game) { ?>
				<tr>
					<td class="center">
						<div class="buttonA"><a id="game_join_<?php echo $Game['ID']; ?>" class="buttonA" href="<?php echo $Game['JoinGameLink']; ?>"><?php if (TIME < $Game['JoinTime']) {?>View Info<?php } else { ?>Join Game<?php } ?></a></div>
					</td>
					<td width="35%"><?php echo $Game['Name']; ?> (<?php echo $Game['ID']; ?>)</td>
					<td class="noWrap"><?php echo $Game['StartDate']; ?></td>
					<td class="noWrap"><?php echo $Game['EndDate']; ?></td>
					<td class="center"><?php echo $Game['Players']; ?></td>
					<td class="center"><?php echo $Game['Type']; ?></td>
					<td class="center"><?php echo $Game['Speed']; ?></td>
					<td class="center"><?php echo $Game['Credits']; ?></td>
				</tr>
			<?php } ?>
		</table><?php
	} else {
		?><p>You have joined all open games.</p><?php
	} ?>
</div>

<br />
<br />
<h1><a href="<?php echo $VotingHref; ?>">Voting</a></h1><?php
if (isset($Voting)) {
	?>Please take a couple of seconds to answer the following question(s) for the SMR Admin team. Thanks!<?php
	foreach ($Voting as $Vote) {
		?><br /><br />
		<form name="FORM" method="POST" action="<?php echo $Vote['HREF'] ?>">
			<span class="bold"><?php echo bbifyMessage($Vote['Question']); ?></span> (<?php echo $Vote['TimeRemaining']; ?> Remaining)<br /><?php
			foreach ($Vote['Options'] as $VoteOption) { ?>
				<input type="radio" name="vote" required value="<?php echo $VoteOption['ID']; ?>"<?php if ($VoteOption['Chosen']) { ?> checked<?php } ?>><?php echo bbifyMessage($VoteOption['Text']); ?> (<?php echo $VoteOption['Votes']; ?> votes)<br /><?php
			} ?>
			<input type="submit" name="submit" value="Vote!"><br />
		</form><?php
	}
} ?>

<br />
<br />
<h1><a href="<?php echo $OldAnnouncementsLink; ?>">View Old Announcements</a></h1>
<br />
<br />

<h1>Previous Games</h1>
<a onclick="$('#prevGames').slideToggle(600);">Show/Hide</a>
<div id="prevGames" class="ajax" style="display:none;"><?php
	if (isset($Games['Previous'])) { ?>
		<table class="standard">
			<tr>
				<th width="150">Game Name</th>
				<th>Start Date</th>
				<th>End Date</th>
				<th>Game Speed</th>
				<th colspan="3">Options</th>
			</tr><?php
			foreach ($Games['Previous'] as $Game) { ?>
				<tr>
					<td width="35%"><?php if (isset($Game['PreviousGameLink'])) { ?><a href="<?php echo $Game['PreviousGameLink']; ?>"><?php } echo $Game['Name']; ?> (<?php echo $Game['ID']; ?>)<?php if (isset($Game['PreviousGameLink'])) { ?></a><?php } ?></td>
					<td><?php echo $Game['StartDate'] ?></td>
					<td><?php echo $Game['EndDate'] ?></td>
					<td class="center"><?php echo $Game['Speed'] ?></td>
					<td class="center"><a href="<?php echo $Game['PreviousGameHOFLink']; ?>">Hall Of Fame</a></td>
					<td class="center"><a href="<?php echo $Game['PreviousGameNewsLink']; ?>">Game News</a></td>
					<td class="center"><?php if (isset($Game['PreviousGameStatsLink'])) { ?><a href="<?php echo $Game['PreviousGameStatsLink']; ?>">Game Stats</a><?php } ?></td>
				</tr>
			<?php } ?>
		</table><?php
	} else {
		?><p>There are no previous games.</p><?php
	} ?>
</div>
