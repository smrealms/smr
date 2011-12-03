<?php
if (isset($Reason))
{
	?><p><big><span class="bold red"><?php echo $Reason; ?></span></big></p><?php
}

if(USE_COMPATIBILITY && !$ThisAccount->hasAllOldAccountIDs())
{ ?>
	<form id="LinkOldAccountForm" method="POST" action="<?php echo $PreferencesFormHREF; ?>">
		<table>
			<tr>
				<th colspan="2">Link Old Account</th>
			</tr>
			
			<tr>
				<td>Login:</td>
				<td><input type="text" name="oldAccountLogin"/></td>
			</tr>
			
			<tr>
				<td>Password:</td>
				<td><input type="password" name="oldAccountPassword"/></td>
			</tr>
			
			<tr>
				<td></td>
				<td><input type="submit" name="action" value="Link Account" id="InputFields" /></td>
			</tr>
		</table>
	</form><?php
}

if(isset($GameID))
{ ?>
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
				<td>Recieve force change messages</td>
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
					<input type="text" maxlength="32" name="PlayerName" value="<?php echo htmlspecialchars($ThisPlayer->getPlayerName()); ?>" size="32"> <?php
					if($ThisPlayer->isNameChanged())
					{
						?>(You have already changed your name for free, further changes will cost <?php echo CREDITS_PER_NAME_CHANGE; ?> SMR Credits)<?php
					}
					else
					{
						?>(You can change your name for free once)<?php
					} ?>
				</td>
			</tr>
	
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="action" value=" Alter Player <?php if($ThisPlayer->isNameChanged()) { ?>(<?php echo CREDITS_PER_NAME_CHANGE; ?> SMR Credits) <?php } ?>" id="InputFields" /></td>
			</tr>
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
			<td>Old Password:</td>
			<td><input type="password" name="old_password" id="InputFields" size="25" /></td>
		</tr>
		
		<tr>
			<td>New Password:</td>
			<td><input type="password" name="new_password" id="InputFields" size="25" /></td>
		</tr>
		
		<tr>
			<td>Retype Password:</td>
			<td><input type="password" name="retype_password" id="InputFields" size="25" /></td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Password" id="InputFields" /></td>
		</tr>
		
		<tr><td colspan="2">&nbsp;</td></tr>
		
		<tr>
			<td>Email address:</td>
			<td><input type="text" name="email" value="<?php echo htmlspecialchars($ThisAccount->getEmail()); ?>" id="InputFields" size="50" /></td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Save and resend validation code" id="InputFields" /></td>
		</tr>
	
		<tr><td colspan="2">&nbsp;</td></tr>

		<tr>
			<td>Cell phone:</td>
			<td><input type="text" name="cell_phone" value="<?php echo htmlspecialchars($ThisAccount->getCellPhone()); ?>" id="InputFields" size="50" /></td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change cell phone" id="InputFields" /></td>
		</tr>

		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		
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
			<td>IRC Nick:</td>
			<td><input type="text" name="irc_nick" value="<?php echo htmlspecialchars($ThisAccount->getIrcNick()); ?>" id="InputFields" size="50" /></td>
		</tr>

		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Nick" id="InputFields" /></td>
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
				for ($i = -12; $i<= 11; $i++)
				{
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
			<td>Font size</td>
			<td><input type="text" size="4" name="fontsize" value="<?php echo $ThisAccount->getFontSize(); ?>" /> Minimum font size is 50%</td>
		</tr>
	
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change Size" id="InputFields" /></td>
		</tr>
	
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>

		<tr>
			<td>Change CSS Link</td>
			<td>
				<input type="text" size="50" name="csslink" value="<?php echo htmlspecialchars($ThisAccount->getCssLink()); ?>"><br />
			</td>
		</tr>

		<tr>
			<td></td>
			<td>
				You should only change this if you know what you're doing.<br />
				If trying to link to a local file you may have to change your browser's security settings.
				For a (somewhat) commented css file to work from look at: <a href="<?php echo URL; ?>/originalCSS/Default.css"><?php echo URL; ?>/originalCSS/Default.css</a><br />
			</td>
		</tr>

		<tr>
			<td>Enable Default CSS</td>
			<td>
				Yes: <input type="radio" name="defaultcss" id="InputFields" value="Yes"<?php if($ThisAccount->isDefaultCSSEnabled()){ ?> checked="checked"<?php } ?> /><br />
				No: <input type="radio" name="defaultcss" id="InputFields" value="No"<?php if(!$ThisAccount->isDefaultCSSEnabled()){ ?> checked="checked"<?php } ?> /><br />
				This specifies whether the default stylesheet (Currently: <a href="<?php echo DEFAULT_CSS; ?>"><?php echo DEFAULT_CSS; ?></a>) should be loaded.<br />
			</td>
		</tr>
		
		<tr>
			<td>Template</td>
			<td>
				<select name="template" id="InputFields"><?php
					foreach(Globals::getAvailableTemplates() as $AvailableTemplate => $ColourSchemes)
					{
						?><option value="<?php echo $AvailableTemplate; ?>"<?php if($ThisAccount->getTemplate()==$AvailableTemplate){ ?>selected="selected"<?php } ?>><?php echo $AvailableTemplate; ?></option><?php
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Colour Scheme</td>
			<td>
				<select name="colour_scheme" id="InputFields"><?php
					foreach(Globals::getAvailableColourSchemes($ThisAccount->getTemplate()) as $ColourScheme)
					{
						?><option value="<?php echo $ColourScheme; ?>"<?php if($ThisAccount->getColourScheme()==$ColourScheme){ ?>selected="selected"<?php } ?>><?php echo $ColourScheme; ?></option><?php
					} ?>
				</select> - This only shows colour schemes available for the current template, if you are changing templates then save the change first to see the colour schemes for the new template.
			</td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="action" value="Change CSS Options" id="InputFields" /></td>
		</tr>
	</table>
	
	
	<table>
		<tr>
			<th colspan="2">Hotkeys (Use space to separate multiple hotkeys)</th>
		</tr><?php
		$MovementTypes = array('Up','Left','Right','Down','Warp');
		$MovementSubTypes = array('Move','Scan');
		foreach($MovementTypes as $MovementType)
		{
			foreach($MovementSubTypes as $MovementSubType)
			{ 
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
				<input type="text" name="amount" id="InputFields" style="width:30px;text-align:center;" /> credits to <?php if(!isset($GameID)){ ?>the account with HoF name of <?php } ?>
				<select name="account_id" id="InputFields"><?php
					foreach($TransferAccounts as $AccID => $AccOrPlayerName)
					{
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