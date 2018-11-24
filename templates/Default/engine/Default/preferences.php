<?php
if (isset($Reason)) {
	?><p><big><span class="bold red"><?php echo $Reason; ?></span></big></p><?php
}

if(isset($GameID)) { ?>
	<form class="standard" id="GamePreferencesForm" method="POST" action="<?php echo $PreferencesFormHREF; ?>">
		<table>
			<tr>
				<th colspan="2">Player Preferences (For Current Game)</th>
			</tr>
	
			<tr>
				<td>Combat drones kamikaze on mines</td>
				<td>
					Yes: <input type="radio" name="kamikaze" id="InputFields" value="Yes"<?php if($ThisPlayer->isCombatDronesKamikazeOnMines()){ ?> checked="checked"<?php } ?> /><br />
					No: <input type="radio" name="kamikaze" id="InputFields" value="No"<?php if(!$ThisPlayer->isCombatDronesKamikazeOnMines()){ ?> checked="checked"<?php } ?> />
				</td>
			</tr>
	
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="action" value="Change Kamikaze Setting" id="InputFields" /></td>
			</tr>
	
			<tr>
				<td>Receive force change messages</td>
				<td>
					Yes: <input type="radio" name="forceDropMessages" id="InputFields" value="Yes"<?php if($ThisPlayer->isForceDropMessages()){ ?> checked="checked"<?php } ?> /><br />
					No: <input type="radio" name="forceDropMessages" id="InputFields" value="No"<?php if(!$ThisPlayer->isForceDropMessages()){ ?> checked="checked"<?php } ?> />
				</td>
			</tr>
	
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="action" value="Change Message Setting" id="InputFields" /></td>
			</tr>
	
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			
			<tr>
				<td>Player Name</td>
				<td>
					<input type="text" maxlength="32" name="PlayerName" value="<?php echo $ThisPlayer->getPlayerName(); ?>" size="32"><br/><?php
					if($ThisPlayer->isNameChanged()) {
						?>(You have already changed your name for free, further changes will cost <?php echo CREDITS_PER_NAME_CHANGE; ?> SMR Credits)<?php
					}
					else {
						?>(You can change your name for free once)<?php
					} ?>
				</td>
			</tr>
	
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="action" value=" Alter Player <?php if($ThisPlayer->isNameChanged()) { ?>(<?php echo CREDITS_PER_NAME_CHANGE; ?> SMR Credits) <?php } ?>" id="InputFields" /></td>
			</tr>

			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>

			<tr>
				<td>Chat Sharing:</td>
				<td><div class="buttonA"><a href="<?php echo $ChatSharingHREF; ?>" class="buttonA">&nbsp;Manage Chat Sharing Settings&nbsp;</a></div></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>These settings specify who you share your game information with in supported chat services.</td>
			<tr>

		</table>
	</form>
	<br /><?php
} ?>
<form id="AccountPreferencesForm" method="POST" action="<?php echo $PreferencesFormHREF; ?>">
	<table>
		<tr>
			<th colspan="2">Account Preferences</th>
		</tr>
		
		<tr>
			<td>Referral Link:</td>
			<td><b><?php echo $ThisAccount->getReferralLink(); ?></b></td>
		</tr>
		
		<tr>
			<td>Login:</td>
			<td><b><?php echo $ThisAccount->getLogin(); ?></b></td>
		</tr>
		
		<tr>
			<td>ID:</td>
			<td><?php echo $ThisAccount->getAccountID(); ?></td>
		</tr>
		
		<tr>
			<td>SMR&nbsp;Credits:</td>
			<td><?php echo $ThisAccount->getSmrCredits(); ?></td>
		</tr>
		
		<tr>
			<td>SMR&nbsp;Reward&nbsp;Credits:</td>
			<td><?php echo $ThisAccount->getSmrRewardCredits(); ?></td>
		</tr>
		
		<tr>
			<td>Ban Points:</td>
			<td><?php echo $ThisAccount->getPoints(); ?></td>
		</tr>
		
		<tr>
			<td>Friendly Colour:</td>
			<td><div id="friendlyColorSelector">
				<div class="preview" style="background-color: #<?php echo $ThisAccount->getFriendlyColour(); ?>"></div>
				<input type="hidden" name="friendly_color" id="InputFields" value="<?php echo $ThisAccount->getFriendlyColour(); ?>"/></div></td>
		</tr>
		
		<tr>
			<td>Neutral Colour:</td>
			<td><div id="neutralColorSelector">
				<div class="preview" style="background-color: #<?php echo $ThisAccount->getNeutralColour(); ?>"></div>
				<input type="hidden" name="neutral_color" id="InputFields" value="<?php echo $ThisAccount->getNeutralColour(); ?>"/></div></td>
		</tr>
		
		<tr>
			<td>Enemy Colour:</td>
			<td><div id="enemyColorSelector">
				<div class="preview" style="background-color: #<?php echo $ThisAccount->getEnemyColour(); ?>"></div>
				<input type="hidden" name="enemy_color" id="InputFields" value="<?php echo $ThisAccount->getEnemyColour(); ?>"/></div></td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Update Colours" id="InputFields" /></td>
		</tr>
		
		<tr>
			<td>Current Password:</td>
			<td><input type="password" name="old_password" id="InputFields" size="25" /></td>
		</tr>
		
		<tr>
			<td>New Password:</td>
			<td><input type="password" name="new_password" id="InputFields" size="25" /></td>
		</tr>
		
		<tr>
			<td>Verify New Password:</td>
			<td><input type="password" name="retype_password" id="InputFields" size="25" /></td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Password" id="InputFields" /></td>
		</tr>
		
		<tr><td colspan="2">&nbsp;</td></tr>
		
		<tr>
			<td>Email address:</td>
			<td><input type="email" name="email" value="<?php echo htmlspecialchars($ThisAccount->getEmail()); ?>" id="InputFields" size="50" /></td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Save and resend validation code" id="InputFields" /></td>
		</tr>
	
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
			<td>Hall of Fame Name:</td>
			<td><input type="text" name="HoF_name" value="<?php echo htmlspecialchars($ThisAccount->getHofName()); ?>" id="InputFields" size="50" /></td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Name" id="InputFields" /></td>
		</tr>
	
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>

		<tr>
			<td>Discord User ID:</td>
			<td><input type="text" name="discord_id" value="<?php echo htmlspecialchars($ThisAccount->getDiscordId()); ?>" id="InputFields" size=50 /></td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Discord ID" id="InputFields" /></td>
		</tr>

		<tr>
			<td>IRC Nick:</td>
			<td><input type="text" name="irc_nick" value="<?php echo htmlspecialchars($ThisAccount->getIrcNick()); ?>" id="InputFields" size="50" /></td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change IRC Nick" id="InputFields" /></td>
		</tr>

		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>

		<tr>
			<td>Timezone:</td>
			<td>
				<select name="timez" id="InputFields"><?php
				$time = TIME;
				$offset = $ThisAccount->getOffset();
				for ($i = -12; $i<= 11; $i++) {
					?><option value="<?php echo $i; ?>"<?php if ($offset == $i){ ?> selected="selected"<?php } ?>><?php echo date(DATE_TIME_SHORT, $time + $i * 3600); ?></option><?php
				} ?>
				</select>
			</td>
		</tr>
	
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Timezone" id="InputFields" /></td>
		</tr>
	
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	
		<tr>
			<td>Date Format:</td>
			<td><input type="text" name="dateformat" value="<?php echo htmlspecialchars($ThisAccount->getShortDateFormat()); ?>" id="InputFields" /><br />(Default: '<?php echo DEFAULT_DATE_DATE_SHORT; ?>')</td>
		</tr>
	
		<tr>
			<td>Time Format:</td>
			<td><input type="text" name="timeformat" value="<?php echo htmlspecialchars($ThisAccount->getShortTimeFormat()); ?>" id="InputFields" /><br />(Default: '<?php echo DEFAULT_DATE_TIME_SHORT; ?>')</td>
		</tr>
	
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Date Formats" id="InputFields" /></td>
		</tr>
	
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		
		<tr>
			<td>Use AJAX (Auto&nbsp;Refresh):</td>
			<td>
				<a href="<?php echo $ThisAccount->getToggleAJAXHREF() ?>"><?php if($ThisAccount->isUseAJAX()){ ?>Disable AJAX (Currently Enabled)<?php }else{ ?>Enable AJAX (Currently Disabled)<?php } ?></a><br />
			</td>
		</tr>
		
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		
		<tr>
			<td>Display Ship Images:</td>
			<td>
				Yes: <input type="radio" name="images" id="InputFields" value="Yes"<?php if($ThisAccount->isDisplayShipImages()){ ?> checked="checked"<?php } ?> /><br />
				No: <input type="radio" name="images" id="InputFields" value="No"<?php if(!$ThisAccount->isDisplayShipImages()){ ?> checked="checked"<?php } ?> /><br />
			</td>
		</tr>
	
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Images" id="InputFields" /></td>
		</tr>
		
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		
		<tr>
			<td>Center Galaxy Map On Player:</td>
			<td>
				Yes: <input type="radio" name="centergalmap" id="InputFields" value="Yes"<?php if($ThisAccount->isCenterGalaxyMapOnPlayer()){ ?> checked="checked"<?php } ?> /><br />
				No: <input type="radio" name="centergalmap" id="InputFields" value="No"<?php if(!$ThisAccount->isCenterGalaxyMapOnPlayer()){ ?> checked="checked"<?php } ?> /><br />
			</td>
		</tr>
	
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Centering" id="InputFields" /></td>
		</tr>
	
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>

		<tr>
			<td>Font size:</td>
			<td><input type="number" size="4" name="fontsize" value="<?php echo $ThisAccount->getFontSize(); ?>" /> Minimum font size is 50%</td>
		</tr>
	
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Size" id="InputFields" /></td>
		</tr>
	
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>

		<tr>
			<td>CSS Template:</td>
			<td>
				<select name="template" id="InputFields"><?php
					foreach(Globals::getAvailableTemplates() as $AvailableTemplate => $ColourSchemes) {
						foreach ($ColourSchemes as $ColourScheme) {
							$selected = ($ThisAccount->getTemplate() == $AvailableTemplate &&
							             $ThisAccount->getColourScheme() == $ColourScheme &&
							             $ThisAccount->isDefaultCSSEnabled()) ? 'selected' : '';
							$name = $AvailableTemplate . ' - ' . $ColourScheme;
							?><option value="<?php echo $name; ?>" <?php echo $selected; ?>><?php echo $name; ?></option><?php
						}
					} ?>
					<option value="None" <?php if (!$ThisAccount->isDefaultCSSEnabled()) { echo 'selected'; } ?>>None</option>
				</select>
			</td>
		</tr>

		<tr>
			<td class="top">Add CSS Link:</td>
			<td>
				<input type="url" size="50" name="csslink" value="<?php echo htmlspecialchars($ThisAccount->getCssLink()); ?>"><br />
				Specifies a CSS file to load in addition to the CSS Template.<br />
				If trying to link to a local file you may have to change your browser's security settings.<br />
				Warning: only add a CSS link if you know what you're doing!
			</td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change CSS Options" id="InputFields" /></td>
		</tr>
	</table>
	<br />
	
	<table>
		<tr>
			<th colspan="2">Hotkeys (Use space to separate multiple hotkeys)</th>
		</tr><?php
		$MovementTypes = array('Up','Left','Right','Down','Warp');
		$MovementSubTypes = array('Move','Scan');
		foreach($MovementTypes as $MovementType) {
			foreach($MovementSubTypes as $MovementSubType) { 
				$FullMovement = $MovementSubType . $MovementType; ?>
				<tr>
					<td><?php echo $MovementSubType, ' ', $MovementType; ?>:</td>
					<td>
						<input type="text" size="50" name="<?php echo $FullMovement; ?>" value="<?php echo htmlentities(implode(' ', $ThisAccount->getHotkeys($FullMovement))); ?>"><br />
					</td>
				</tr><?php
			}
		} ?>
		<tr>
			<td>Scan Current Sector:</td>
			<td>
				<input type="text" size="50" name="ScanCurrent" value="<?php echo htmlentities(implode(' ', $ThisAccount->getHotkeys('ScanCurrent'))); ?>"><br />
			</td>
		</tr>
		<tr>
			<td>Show Current Sector:</td>
			<td>
				<input type="text" size="50" name="CurrentSector" value="<?php echo htmlentities(implode(' ', $ThisAccount->getHotkeys('CurrentSector'))); ?>"><br />
			</td>
		</tr>
		<tr>
			<td>Show Local Map:</td>
			<td>
				<input type="text" size="50" name="LocalMap" value="<?php echo htmlentities(implode(' ', $ThisAccount->getHotkeys('LocalMap'))); ?>"><br />
			</td>
		</tr>
		<tr>
			<td>Show Plot Course:</td>
			<td>
				<input type="text" size="50" name="PlotCourse" value="<?php echo htmlentities(implode(' ', $ThisAccount->getHotkeys('PlotCourse'))); ?>"><br />
			</td>
		</tr>
		<tr>
			<td>Show Current Players:</td>
			<td>
				<input type="text" size="50" name="CurrentPlayers" value="<?php echo htmlentities(implode(' ', $ThisAccount->getHotkeys('CurrentPlayers'))); ?>"><br />
			</td>
		</tr>
		<tr>
			<td>Enter Port:</td>
			<td>
				<input type="text" size="50" name="EnterPort" value="<?php echo htmlentities(implode(' ', $ThisAccount->getHotkeys('EnterPort'))); ?>"><br />
			</td>
		</tr>
		<tr>
			<td>Attack Trader/Continue Attack</td>
			<td>
				<input type="text" size="50" name="AttackTrader" value="<?php echo htmlentities(implode(' ', $ThisAccount->getHotkeys('AttackTrader'))); ?>"><br />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Save Hotkeys" id="InputFields" /></td>
		</tr>
	</table>
</form><br />

<form id="TransferSMRCreditsForm" method="POST" action="<?php echo $PreferencesConfirmFormHREF; ?>">
	<table>
		<tr>
			<th colspan="2">SMR Credits</th>
		</tr>
		<tr>
			<td>Transfer Credits:</td>
			<td>
				<input type="number" name="amount" id="InputFields" style="width:50px;" class="center" /> credits to <?php if(!isset($GameID)){ ?>the account with HoF name of <?php } ?>
				<select name="account_id" id="InputFields"><?php
					foreach($TransferAccounts as $AccID => $AccOrPlayerName) {
						?><option value="<?php echo $AccID; ?>"><?php echo $AccOrPlayerName; ?></option><?php
					} ?>
				</select>
			</td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Transfer" id="InputFields" /></td>
		</tr>
	</table>
</form>

<script type="text/javascript" src="js/colorpicker.js"></script>
