Each Space Merchant Realms round requires you to create a new Trader.<br />
To do this you must choose a name for yourself and select your race.<br />

<br /><?php
if ($Game->getDescription()) { ?>
	<h2>Round Description</h2>
	<p><?php echo bbifyMessage($Game->getDescription()); ?></p><?php
}?>

<table class="standard">
	<tr class="center">
		<th>Start Date</th>
		<th>End Date</th>
		<th>Max Turns</th>
		<th>Start Turn Hours</th>
		<th>Max Players</th>
		<th>Alliance Max Players</th>
		<th>Alliance Max Vets</th>
		<th>View Warp Chart</th>
	</tr>
	<tr class="center">
		<td width="12%"><?php echo date(DATE_FULL_SHORT_SPLIT, $Game->getStartTime()); ?></td>
		<td width="12%"><?php echo date(DATE_FULL_SHORT_SPLIT, $Game->getEndTime()); ?></td>
		<td><?php echo $Game->getMaxTurns(); ?></td>
		<td><?php echo $Game->getStartTurnHours(); ?></td>
		<td><?php echo $Game->getMaxPlayers(); ?></td>
		<td><?php echo $Game->getAllianceMaxPlayers(); ?></td>
		<td><?php echo $Game->getAllianceMaxVets(); ?></td>
		<td>
			<a href="map_warps.php?game=<?php echo $Game->getGameID(); ?>" target="_blank">
				<img src="images/warp_chart.svg" height="24" width="24" style="vertical-align: middle;" />
			</a>
		</td>
	</tr>
</table><br/>
<table class="standard">
	<tr class="center">
		<th>Type</th>
		<th>Game Speed</th>
		<th>Credits Required</th>
		<th>Stats Ignored</th>
		<th>Starting Credits</th>
	</tr>
	<tr class="center">
		<td><?php echo $Game->getGameType(); ?></td>
		<td><?php echo $Game->getGameSpeed(); ?></td>
		<td><?php echo $Game->getCreditsNeeded(); ?></td>
		<td><?php echo $Game->isIgnoreStats() ? 'Yes' : 'No'; ?></td>
		<td><?php echo number_format($Game->getStartingCredits()); ?></td>
	</tr>
</table><br />

<?php
if (!isset($JoinGameFormHref)) { ?>
	<p class="bold big">
		Time until you can join this game: <?php echo format_time($Game->getJoinTime() - TIME); ?>
		<br /><br /><?php
		if ($Game->getStartTime() == $Game->getJoinTime()) { ?>
			The game will start immediately at this time!<?php
		} else { ?>
			Note: You will not be able to start moving until the game starts <?php echo format_time($Game->getStartTime() - $Game->getJoinTime()); ?> later!<?php
		} ?>
	</p><?php
	return;
} ?>

<form name="JoinGameForm" method="POST" action="<?php echo $JoinGameFormHref; ?>">

	<table class="nobord nohpad">
		<tr>
			<td>
				<h2>Rules</h2>
				<p>The following Trader names are <span class="red">prohibited</span>:</p>
				<ul>
					<li>Names that convey an attitude towards yourself or someone else - such as "Lamer" or "Shadow Sucks".</li>
					<li>Names that make excessive use of special characters, eg. "~-=[Daron]=-~" should be "Daron" instead.</li>
					<li>Names that look similar or identical to another player in an attempt to trick other players.</li>
					<li>Names with references to "out of character" information or that would make sense only to the player, not the character - such as "SpaceGamer" or "SMR Rules".</li>
				</ul>
				Names that violate these rules will be changed by the admins and, in extreme cases, abuse of the naming process will result in your account being banned.
				<br />
				For more information and a complete list of game rules, consult the Space Merchant Realms Wiki under <a href="<?php echo WIKI_URL; ?>/rules" target="_blank">Rules<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Rules"/></a>
			<br /><br />

				<h2>Choosing Your Race</h2>
				<p>The race you select for your Trader affects the following:</p>
				<ul>
					<li>The <span class="yellow">galaxy</span> you will start the game in.</li>
					<li>The <span class="yellow">ports</span> where you can trade - you can only trade with your own race and races you have peace with.</li>
					<li>The <span class="yellow">ships</span> you can purchase - you cannot purchase a ship of another race.</li>
					<li>The <span class="yellow">weapons</span> you can arm your ship with - you can only buy weapons from your own race and races you have peace with.</li>
				</ul>
				Races that appear strongest may have certain disadvantages while races that appear weakest have special benefits. Listed below are some of the basic characteristics of each race.<br />
				<ul>
					<li><span class="yellow">Alskant</span> - Large variety of hardware but no dedicated warship. Trade bonuses with all races.</li>
					<li><span class="yellow">Creonti</span> - Cute and cuddly with lots of firepower. Reinforced ships with heavy armour plating.</li>
					<li><span class="yellow">Human</span> - Jump drive technology which enables fast inter-galactic movement.</li>
					<li><span class="yellow">Ik'Thorne</span> - Most overall defense, relying heavily on swarms of combat drones.</li>
					<li><span class="yellow">Salvene</span> - Illusion Generator technology which allows ships to mask their full strength.</li>
					<li><span class="yellow">Thevian</span> - Fastest racial ships in the universe.</li>
					<li><span class="yellow">WQ Human</span> - Cloaking Device technology which allows ships to hide from lower level traders.</li>
					<li><span class="yellow">Nijarin</span> - High firepower and Drone Communication Scrambler technology offsets lower defenses.</li>
				</ul>
				A full description and ship list for each race can be found on the SMR Wiki <a href="<?php echo WIKI_URL; ?>/game-guide/races" target="_blank">Races<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Races"/></a> page.
				<br /><br />

				<h2>Create Your Trader</h2>
				<p>Now it is time for you to create your Trader and begin your quest for riches, fame and glory! Where will your destiny take you?</p>
				<table class="centered">
					<tr>
						<td class="right"><b>Name:</b>&nbsp;</td>
						<td><input required type="text" name="player_name" maxlength="32" class="InputFields" /></td>
						<td rowspan="4">&nbsp;&nbsp;</td>
						<td rowspan="4" class="standard top">
							<img id="race_image" src="images/race/race<?php echo $SelectedRaceID; ?>.jpg" width="282" height="360" alt="" />
						</td>
					</tr>
					<tr>
						<td class="right"><b>Race:</b>&nbsp;</td>
						<td>
						<select name="race_id" class="InputFields" OnChange="showRaceInfo(this);">
							<?php /*<option value="1">[please select]</option> */
							foreach ($Races as $Race) {
								?><option value="<?php echo $Race['ID']; if ($Race['Selected']) { ?>" selected="selected<?php } ?>"><?php echo $Race['Name']; ?> (<?php echo $Race['NumberOfPlayers']; ?> Traders)<?php
							} ?>
						</select>
						</td>
					</tr>
					
					<tr>
						<td>&nbsp;</td>
						<td><input type="submit" name="action" value="Create Player" class="InputFields" /></td>
					</tr>
					
					<tr>
						<td colspan="2" style="width:300px; height:315px;" class="top">
							<div id="race_descr">
								<?php
								foreach ($Races as $Race) { ?>
									<span class="race_descr<?php echo $Race['ID']; if (!$Race['Selected']) { ?> hide<?php } ?>"><?php echo $Race['Description']; ?></span><?php
								} ?>
							</div>
						</td>
					</tr>
					
				</table>
			</td>
		</tr>

		<tr>
			<td>
				<table class="center">
					<tr>
						<td colspan="3">Trading</td>
					</tr>
					<tr>
						<td>Combat<br />Strength</td>
						<td>
							<img width="440" height="440" id="graphframe" src="images/race/graph/graph<?php echo $SelectedRaceID; ?>.gif" alt="Race overview" />
						</td>
						<td>Hunting</td>
					</tr>
					<tr>
						<td colspan="3">Utility</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
