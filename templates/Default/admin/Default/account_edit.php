<form name="form_acc" method="POST" action="<?php echo $EditFormHREF; ?>">
	<p>
		<table cellpadding="3" border="0">
			<tr>
				<td align="right" class="bold">Account ID:</td>
				<td><?php
					if(isset($EditingAccount)) {
						echo $EditingAccount->getAccountID();
					}
					else { ?>
						<input type="text" name="account_id" id="InputFields" size="5"><?php
					} ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="bold">Login:</td>
				<td><?php
					if(isset($EditingAccount)) {
						echo $EditingAccount->getLogin();
					}
					else { ?>
						<input type="text" name="login" id="InputFields" size="20"><?php
					} ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="bold">Validation Code:</td>
				<td><?php
					if(isset($EditingAccount)) {
						echo $EditingAccount->getValidationCode();
					}
					else { ?>
						<input type="text" name="val_code" id="InputFields" size="20"><?php
					} ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="bold">Email:</td>
				<td><?php
					if(isset($EditingAccount)) {
						echo $EditingAccount->getEmail();
					}
					else { ?>
						<input type="text" name="email" id="InputFields" size="20"><?php
					} ?>
				</td>
			</tr>
			<tr>
				<td align="right" class="bold">HoF Name:</td>
				<td><?php
					if(isset($EditingAccount)) {
						echo $EditingAccount->getHofName();
					}
					else { ?>
						<input type="text" name="hofname" id="InputFields" size="20"><?php
					} ?>
				</td>
			</tr><?php

			if(isset($EditingAccount)) { ?>
				<tr>
				<td align="right" class="bold">Points:</td>
				<td><?php echo $EditingAccount->getPoints(); ?></td>
				</tr><?php
			} ?>

			<tr>
				<td colspan="2">&nbsp;</td>
			</tr><?php

			if(isset($EditingAccount)) { ?>
				<tr>
					<td align="right" valign="top" class="bold">Player:</td>
						<td><?php
							if(count($EditingPlayers)) { ?>
								<a onclick="$('#accountPlayers').fadeToggle(600);">Show/Hide</a>
								<table id="accountPlayers" style="display:none"><?php
									foreach($EditingPlayers as &$CurrentPlayer) {
										$CurrentShip =& $CurrentPlayer->getShip(); ?>
										<tr>
											<td align="right">Game ID:</td>
											<td><?php echo $CurrentPlayer->getGameID(); ?></td>
										</tr>
										<tr>
											<td align="right">Name:</td>
											<td><input type=text name=player_name[<?php echo $CurrentPlayer->getGameID(); ?>] value="<?php echo $CurrentPlayer->getPlayerName(); ?>" />(<?php echo $CurrentPlayer->getPlayerID(); ?>)</td>
										</tr>
										<tr>
											<td align="right">Experience:</td>
											<td><?php echo number_format($CurrentPlayer->getExperience()); ?></td>
										</tr>
										<tr>
											<td align="right">Ship:</td>
											<td><?php echo $CurrentShip->getName(); ?> (<?php echo $CurrentShip->getAttackRating(); ?>/<?php echo $CurrentShip->getDefenseRating(); ?>)</td>
										</tr>
										<tr>
											<td><input type="radio" name="delete[<?php echo $CurrentPlayer->getGameID(); ?>]" value="TRUE" unchecked="unchecked">Yes<input type="radio" name="delete[<?php echo $CurrentPlayer->getGameID(); ?>]" value="FALSE" checked="checked">No</td>
											<td>Delete player</td>
										</tr><?php
									} ?>
								</table><?php
							}
							else { ?>
								Joined no active games</td><?php
							} ?>
					</td>

				</tr>

				<tr>
					<td>&nbsp;</td><td><hr noshade style="height:1px; border:1px solid white;"></td>
				</tr>

				<tr>
					<td align="right" valign="top" class="bold">Donation:</td>
					<td><input type="text" name="donation" size="5" id="InputFields" class="center">$</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="checkbox" name="smr_credit" checked="checked"> Grant SMR Credits</td>
				</tr>

				<tr>
					<td align="right" valign="top" class="bold">Grant Reward SMR Credits:</td>
					<td><input type="text" name="grant_credits" size="5" id="InputFields" class="center"> Credits</td>
				</tr>

				<tr>
					<td>&nbsp;</td>
					<td><hr noshade style="height:1px; border:1px solid white;"></td>
				</tr>

				<script type="text/javascript">
					function go() {
						var val = window.document.form_acc.reason_pre_select.value;
						if (val == 2) {
							alert("Please use the following syntax when you enter the multi closing info: 'Match list:1+5+7' Thanks");
							window.document.form_acc.suspicion.value = 'Match list:';
							window.document.form_acc.suspicion.disabled = false;
							window.document.form_acc.suspicion.focus();
						} else {
							window.document.form_acc.suspicion.value = 'For Multi Closings Only';
							window.document.form_acc.suspicion.disabled = true;
						}
						window.document.form_acc.choise[0].checked = true;
					}
				</script>
				<tr>
					<td align="right" valign="top" class="bold">Close Reason:</td>
					<td>
						<p>Reopen type:<input type="radio" name="reopen_type" value="account">Account close <input type="radio" name="reopen_type" value="mail">Mail ban</p>
						<p>
							<input type="radio" name="choise" value="pre_select">
							<select name="reason_pre_select" onchange="go()">
								<option value="0">[Please Select]</option><?php
								$Disabled = $EditingAccount->isDisabled();
								foreach($BanReasons as $ReasonID => $BanReason) { ?>
									<option value="<?php echo $ReasonID; ?>"<?php if($Disabled !== false && $ReasonID == $Disabled['ReasonID']) { ?> selected="selected"<?php } ?>><?php echo $BanReason; ?></option><?php
								} ?>
							</select>
						</p>
						<p>
							<input type="radio" name="choise" value="individual">
							<input type="text" name="reason_msg" id="InputFields" style="width:400px;">
						</p>
						<p><input type="radio" name="choise" value="reopen">Reopen!</p>
						<p><input type=text name=suspicion id="InputFields" disabled=true style="width:400px;" value="For Multi Closings Only"></p>
						<p>Mail ban: <input type="text" name="mailban" id="InputFields" class="center" style="width:30px;"> days</p>
						<p>Points: <input type="text" name="points" id="InputFields" class="center" style="width:30px;"> points</p><?php
						if ($Disabled !== false) {
							echo $Disabled['Reason'];
							if ($Disabled['Time'] > 0) { ?>
								<p>The account is set to reopen on <?php echo date(DEFAULT_DATE_FULL_LONG, $Disabled['Time']); ?>.</p><?php
							}
							else { ?>
								<p>The account is set to never reopen.</p><?php
							}
						} ?>
					</td>
				</tr>

				<tr>
					<td>&nbsp;</td>
					<td><hr noshade style="height:1px; border:1px solid white;"></td>
				</tr>

				<tr>
					<td align="right" valign="top" class="bold">Closing History:</td>
					<td><?php
						if(count($ClosingHistory) > 0) {
							foreach($ClosingHistory as $Action) {
								echo date(DATE_FULL_SHORT, $Action['Time']); ?> - <?php echo $Action['Action']; ?> by <?php echo $Action['AdminName']; ?><br /><?php
							}
						}
						else { ?>
							No activity.<?php
						} ?>
					</td>
				</tr>

				<tr>
					<td>&nbsp;</td>
					<td><hr noshade style="height:1px; border:1px solid white;"></td>
				</tr>

				<tr>
					<td align="right" valign="top" class="bold">Exception:</td>
					<td><?php
						if (isset($Exception)) {
							echo $Exception;
						}
						else { ?>
							This account is not listed.<br /><input type="text" name="exception_add" value="Add An Exception"><?php
						} ?>
					</td>
				</tr>

				<tr>
					<td>&nbsp;</td>
					<td><hr noshade style="height:1px; border:1px solid white;"></td>
				</tr>

				<tr>
					<td align="right" valign="top" class="bold">Forced Veteran:</td>
					<td><input type="radio" name="veteran_status" value="TRUE"<?php if($EditingAccount->isVeteranBumped()) { ?> checked="checked"<?php } ?>>Yes</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="radio" name="veteran_status" value="FALSE"<?php if(!$EditingAccount->isVeteranBumped()) { ?> checked="checked"<?php } ?>>No</td>
				</tr>

				<tr>
					<td>&nbsp;</td>
					<td><hr noshade style="height:1px; border:1px solid white;"></td>
				</tr>

				<tr>
					<td align="right" valign="top" class="bold">Logging:</td>
					<td><input type="radio" name="logging_status" value="TRUE"<?php if($EditingAccount->isLoggingEnabled()) { ?> checked="checked"<?php } ?>>Yes</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="radio" name="logging_status" value="FALSE"<?php if(!$EditingAccount->isLoggingEnabled()) { ?> checked="checked"<?php } ?>>No</td>
				</tr>

				<tr>
					<td>&nbsp;</td>
					<td><hr noshade style="height:1px; border:1px solid white;"></td>
				</tr>

				<tr>
					<td align="right" valign="top" class="bold">Last IP's:</td>
					<td><?php
						if(count($RecentIPs) > 0) { ?>
							<a onclick="$('#recentIPs').fadeToggle(600);">Show/Hide</a>
							<table id="recentIPs" style="display:none"><?php
								foreach($RecentIPs as $RecentIP) { ?>
									<tr>
										<td><?php echo date(DATE_FULL_SHORT, $RecentIP['Time']); ?></td>
										<td>&nbsp;</td>
										<td><?php echo $RecentIP['IP']; ?></td>
										<td>&nbsp;</td>
										<td><?php echo $RecentIP['Host']; ?></td>
									</tr><?php
								} ?>
							</table><?php
						} ?>
					</td>
				</tr><?php
			}
			else { ?>
				<tr>
					<td align="right" class="bold">Player Name:</td>
					<td><input type="text" name="player_name" id="InputFields" size="20"></td>
				</tr><?php
			} ?>

		</table>
	</p>

<table>
	<tr>
		<td><?php
			if(isset($EditingAccount)) { ?>
				<input type="submit" name="action" value="Edit Account" id="InputFields" /><?php
			}
			else { ?>
				<input type="submit" name="action" value="Search" id="InputFields" /><?php
			} ?>
		</td><?php

		if(isset($EditingAccount)) { ?>
			<td>
				<div class="buttonA"><a class="buttonA" href="<?php echo $ResetFormHREF; ?>">&nbsp;Reset Form&nbsp;</a></div>
			</td><?php
		} ?>
</table>
</form><?php

if(isset($ErrorMessage)) { ?>
	<div align="center"><?php echo $ErrorMessage; ?></div><?php
}

?>
<div align="center"><?php echo $Message; ?></div>