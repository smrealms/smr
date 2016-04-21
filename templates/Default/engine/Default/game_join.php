	Each Space Merchant Realms round requires you to create a new Trader.<br />
	To do this you must choose a name for yourself and select your race.<br />
	<br />
	<h1>Join <?php echo $Game['GameName'] ?> (<?php echo $Game['ID']; ?>)</h1><br />
<table class="standard">
	<tr>
		<th>Start Date</th>
		<th>Start Turns Date</th>
		<th>End Date</th>
		<th>Max Turns</th>
		<th>Start Turn Hours</th>
		<th>Max Players</th>
		<th>Alliance Max Players</th>
		<th>Alliance Max Vets</th>
	</tr>
	<tr>
		<td width="12%"><?php echo date(DATE_FULL_SHORT_SPLIT,$Game['StartDate']); ?></td>
		<td width="12%"><?php echo date(DATE_FULL_SHORT_SPLIT,$Game['StartTurnsDate']); ?></td>
		<td width="12%"><?php echo date(DATE_FULL_SHORT_SPLIT,$Game['EndDate']); ?></td>
		<td><?php echo $Game['MaxTurns']; ?></td>
		<td><?php echo $Game['StartTurnHours']; ?></td>
		<td><?php echo $Game['GameMaxPlayers']; ?></td>
		<td><?php echo $Game['AllianceMaxPlayers']; ?></td>
		<td><?php echo $Game['AllianceMaxVets']; ?></td>
	</tr>
</table><br/>
<table class="standard">
	<tr>
		<th>Type</th>
		<th>Game Speed</th>
		<th>Credits Required</th>
		<th>Stats Ignored</th>
		<th>Starting Credits</th>
	</tr>
	<tr>
		<td><?php echo $Game['GameType']; ?></td>
		<td><?php echo $Game['Speed']; ?></td>
		<td><?php echo $Game['GameCreditsRequired']; ?></td>
		<td><?php echo $Game['IgnoreStats']?'Yes':'No'; ?></td>
		<td><?php echo number_format($Game['StartingCredits']); ?></td>
	</tr>
</table><br /><?php
if($Game['GameDescription']) { ?>
	<h2>Round Description</h2>
	<p><?php echo bbifyMessage($Game['GameDescription']); ?></p><?php
}?>
<form<?php if(isset($JoinGameFormHref)){ ?> name="JoinGameForm" method="POST" action="<?php echo $JoinGameFormHref; ?>"<?php } ?>>

	<h2>Rules</h2>
	<table class="nobord nohpad">
		<tr>
			<td>
				<p>
					Each Space Merchant Realms round requires you to create a new Trader.<br />
					To do this you must choose a name for yourself and select your race.<brt />
					The following rules apply to Trader names:<br />
					<ul>
						<li>Names that convey an attitude towards yourself or someone else - such as "Lamer" or "Shadow Sucks".</li>
						<li>Names that make excessive use of special characters, eg. "~-=[Daron]=-~" should be "Daron" instead.</li>
						<li>Names that look similar or identical to another player in an attempt to trick other players are prohibited.</li>
						<li>Names with references to "out of character" information - ie. something that would make sense only to the player, not the character - such as "SpaceGamer", "SMR Rules" etc.</li>
						<li>Names that violate these rules will be changed by the admins and require you to change your name, in extreme cases, abuse of the naming process will result in your account being banned.</li>
					</ul>
					If you disregard these rules, your player will be deleted, so choose your name wisely.<br /><br />
					All Space Merchant Realms Rules can be viewed on the Space Merchant Realms Wiki under <a href="http://wiki.smrealms.de/index.php?title=Space_Merchant_Realms_v1.6_Rules" target="_blank">Rules<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Rules"/></a>
					<br />
				</p>	
				<h2>Choosing Your Race</h2>
				<p>
					Choosing your race is the first decision you will make. When selecting your race you need to take several things into consideration.<br />
					<ul>
						<li>1. Affects what galaxy you will start the game in.</li>
						<li>2. Affects what ports you can trade (as you can only trade with your own race and those you have peace with)</li>
						<li>3. Affects what ships you can purchase (You cannot purchase a ship of another race)</li>
						<li>4. Affects what weapons you can arm your ship with Other races weapons can be purchased once peaceful relations have been established between your races)</li>
					</ul>
					Each race is defined by their ships. Some races build there ships for trading, while others for combat.<br />
					<br />
					Each race has unique characteristics that cannot be represented by the graphs to be shown here. Races that appear strongest may have certain disadvantages while races that appear weakest have special benefits. Listed below are some of the basic characteristics of each race.<br />
					<br />
					A full description including benefits and disadvantages as well as ship lists of each race can be seen by accessing SMR Wiki <a href="http://wiki.smrealms.de/index.php?title=Game_Guide:_Races" target="_blank">Races<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Races"/></a> page.<br /><br />
				<ul>
					<li>Alskant - Large variety of hardware but no dedicated warship. Trade bonus with Neutral ports.</li>
					<li>Creonti - Cute and cuddly with lots of firepower. Ships' defences consist predominantly of armour.</li>
					<li>Human - Race with the best mining ship and jump drive technology which enables fast inter-galactic movement.</li>
					<li>Ik'Thorne - Most overall defence. Ships' offence and defence rely on combat drones.</li>
					<li>Salvene - Illusion Generator technology which allows ships to mask their full strength.</li>
					<li>Thevian - Fastest racial ships in the universe.</li>
					<li>WQ Human - Cloaking Device technology which allows ships to hide from lower level traders</li>
					<li>Nijarin - High firepower and Drone Communication Scrambler technology offsets lower defences.</li>
				</ul>
				</p>
				<h2>Create your trader</h2>
				<p>Now it is time for you to create your Trader and begin your quest for riches, fame and glory! Where will your destiny take you?</p>
				<table>
					<tr>
						<td align="right"><b>Name:</b></td>
						<td><input type="text" name="player_name" maxlength="32" class="InputFields"<?php if(!isset($JoinGameFormHref)){ ?>disabled="disabled"<?php } ?>></td>
						<td rowspan="4" class="standard"><img id="race_image" name="race_image" src="images/race/race1.gif" alt="Please select a race."></td>
					</tr>
					<tr>
						<td align="right"><b>Race:</b></td>
						<td>
						<select name="race_id" size="1" style="border-width:0px;width:150px;" OnChange="go();">
							<?php /*<option value="1">[please select]</option> */
							foreach($Races as $Race) {
								?><option value="<?php echo $Race['ID']; if($Race['Selected']){ ?>" selected="selected<?php } ?>"><?php echo $Race['Name']; ?> (<?php echo $Race['NumberOfPlayers']; ?> Traders)<?php
							} ?>
						</select>
						</td>
					</tr>
					
					<tr>
						<td align="right">&nbsp;</td>
						<td><?php
						if(isset($JoinGameFormHref)) {
							?><input type="submit" name="action" value="Create Player" class="InputFields" /><?php
						}
						else {
							?><b>This game has not started yet.</b><?php
						} ?>
						</td>
					</tr>
					
					<tr>
						<td colspan="2">
							<div id="race_descr" class="InputFields" style="width:300px;height:275px;border:0;"></div>
						</td>
					</tr>
					
				</table>
			</td>
		</tr>
					
		<tr>
			<td align=center>
				<table>
					<tr>
						<td align=center colspan=4 class="center">Trading</td>
					</tr>
					<tr>
						<td align=left>Combat<br />
						Strength</td>
						<td align=center colspan=2>
							<img width="440" height="440" border="0" name="graph" id="graphframe" src="images/race/graph/graph1.gif" alt="Race overview" />
						</td>
						<td align=right>Hunting</td>
					</tr>
					<tr>
						<td align=center colspan=4 class="center">Utility</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript">
var	desc = new Array(<?php echo $RaceDescriptions; ?>);
function go() {
	var race_id = document.forms[0].race_id.options[document.forms[0].race_id.selectedIndex].value;
	document.getElementById('race_image').src = "images/race/race" + race_id + ".jpg";
	document.getElementById('graphframe').src = "images/race/graph/graph" + race_id + ".gif";
	document.getElementById('race_descr').innerHTML = desc[race_id - 1];
}
go();
</script>
