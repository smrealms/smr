<h1>Play Game</h1>

<a href="{$UserRankName}"><b style="color:yellow;">Rankings</b></a>
<br />You are ranked as a{if $UserRankName[0] == 'a' || $UserRankName[0] == 'A'}n{/if} <span style="font-size:125%;color:greenyellow;">{$UserRankName}</span> player.<p><br /><br />


{if $Games.Play}
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
		</tr>
		{foreach from=$Games.Play item=Game}
			<tr>
				<td>
					<div class="buttonA">
						<a class="buttonA" href="{$Game.PlayGameLink}">&nbsp;Play Game&nbsp;</a>
					</div>
				</td>
				<td width="35%"><a href="{$Game.GameStatsLink}">{$Game.Name} ({$Game.ID})</a></td>
				
				<td>{$Game.Maintenance}</td>
				<td>{$Game.NumberPlaying}</td>
				<td>{$Game.LastActive}</td>
				<td>{$Game.LastMovement}</td>
				<td>{$Game.EndDate}</td>
				<td>{$Game.Type}</td>
			</tr>
		{/foreach}
	</table><br />
	<br />
{/if}
<h1>Join Game</h1>
{if $Games.Join}
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
		</tr>
		{foreach from=$Games.Join item=Game}
			<tr>
				<td>
					<div class="buttonA"><a class="buttonA" href="{$Game.JoinGameLink}">&nbsp;Join Game&nbsp;</a></div>
				</td>
				<td width="35%">{$Game.Name} ({$Game.ID})</td>
				<td>{$Game.StartDate}</td>
				<td>{$Game.EndDate}</td>
				<td>{$Game.MaxPlayers}</td>
				<td>{$Game.Type}</td>
				<td>{$Game.Speed}</td>
				<td>{$Game.Credits}</td>
			</tr>
		{/foreach}
	</table><br />
	<br />
{else}
<p>You have joined all open games.</p>
{/if}
<br />
<br />
<h1>Previous Games</h1>
{if $Games.Previous}
	<table class="standard" cellspacing="0">
		<tr>
			<th width="150">Game Name</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Game Speed</th>
			<th colspan="3">Options</th>
		</tr>
		</tr>
		{foreach from=$Games.Previous item=Game}
			<tr>
				<td width="35%"><a href="{$Game.PreviousGameLink}">{$Game.Name} ({$Game.ID})</a></td>
				<td>{$Game.StartDate}</td>
				<td>{$Game.EndDate}</td>
				<td>{$Game.Speed}</td>
				<td><a href="{$Game.PreviousGameHOFLink}">Hall Of Fame</a></td>
				<td><a href="{$Game.PreviousGameNewsLink}">Game News</a></td>
				<td><a href="{$Game.PreviousGameStatsLink}">Game Stats</a></td>
			</tr>
		{/foreach}
	</table><br />
	<br />
{else}
<p>There are no previous games.</p>
{/if}
<br />
<br />
<h1>Donate Money</h1>
<p>
	<a href="{$DonateLink}"><img src="images/donation.jpg" border="0" /></a>
</p>
<br />
<a href="{$OldAnnouncementsLink}"><h1>View Old Announcements</h1></a>
<br />
<br />

{if $AdminPermissions}
	<h1>Admin Privileges</h1><br />
	<ul>
	{foreach from=$AdminPermissions item=Permission}
		<li><a href="{$Permission.PermissionLink}">{$Permission.Name}</a></li>
	{/foreach}
	</ul>
{/if}