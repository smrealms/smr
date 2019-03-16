<form name="form_acc" method="POST" action="<?php echo $EditFormHREF; ?>">
	<table cellpadding="3" border="0">
		<tr>
			<td align="right" class="bold">Account ID:</td>
			<td><?php
				if(isset($EditingAccount)) {
					echo $EditingAccount->getAccountID();
				}
				else { ?>
					<input type="number" name="account_id" class="InputFields" size="5"><?php
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
					<input type="text" name="login" class="InputFields" size="20"><?php
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
					<input type="text" name="val_code" class="InputFields" size="20"><?php
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
					<input type="email" name="email" class="InputFields" size="20"><?php
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
					<input type="text" name="hofname" class="InputFields" size="20"><?php
				} ?>
			</td>
		</tr><?php

		if(isset($EditingAccount)) { ?>
			<tr>
			<td align="right" class="bold">Points:</td>
			<td><?php echo $EditingAccount->getPoints(); ?></td>
			</tr>
			<tr>
				<td class="top right bold">Status:</td>
				<td><?php
					if ($Disabled) { ?>
						<span class="red">CLOSED</span> (<?php echo $Disabled['Reason']; ?>)<br />
						The account is set to <?php
						if ($Disabled['Time'] > 0) { ?>
							reopen on <?php echo date(DEFAULT_DATE_FULL_LONG, $Disabled['Time']); ?>.<?php
						} else { ?>
							never reopen.</p><?php
						}
					} else { ?>
						<span class="green">OPEN</span><?php
					} ?>
				</td>
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
								foreach ($EditingPlayers as $CurrentPlayer) {
									$CurrentShip = $CurrentPlayer->getShip(); ?>
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
				<td><input type="number" name="donation" size="5" class="InputFields center">$</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="checkbox" name="smr_credit" checked="checked"> Grant SMR Credits</td>
			</tr>

			<tr>
				<td align="right" valign="top" class="bold">Grant Reward SMR Credits:</td>
				<td><input type="number" name="grant_credits" size="5" class="InputFields center"> Credits</td>
			</tr>

			<tr>
				<td>&nbsp;</td>
				<td><hr noshade style="height:1px; border:1px solid white;"></td>
			</tr>

			<tr>
				<td align="right" valign="top" class="bold">Close Account:</td>
				<td>
					<table>
						<tr>
							<td><input type="radio" name="special_close" value="<?php echo CLOSE_ACCOUNT_BY_REQUEST_REASON; ?>"></td>
							<td>
								<b>Close by User Request</b><br />
								Users will be able to re-open their account by themselves if this
								account closing method is used. It is useful if, e.g., they do not
								want to receive any more newsletters.
							</td>
						</tr>
						<tr>
							<td><input type="radio" name="special_close" value="<?php echo CLOSE_ACCOUNT_INVALID_EMAIL_REASON; ?>"></td>
							<td>
								<b>Close due to Invalid E-mail</b><br />
								Use this if the e-mail address for this account no longer exists,
								e.g. if we get a newsletter bounce. Users can re-open their account
								by providing a new e-mail address.
							</td>
						</tr>
					</table>
					<p>Note (optional): <input type="text" name="close_by_request_note" class="InputFields" /></p>
				</td>
			</tr>

			<tr>
				<td>&nbsp;</td>
				<td><hr noshade style="height:1px; border:1px solid white;"></td>
			</tr>

			<script type="text/javascript">
				function go() {
					var val = window.document.form_acc.reason_pre_select.value;
					if (val == 2) {
						alert("Please use the following syntax when you enter the multi closing suspicion: 'Match list:1+5+7' Thanks");
						window.document.form_acc.suspicion.value = 'Match list:';
						window.document.form_acc.suspicion.disabled = false;
						window.document.form_acc.suspicion.focus();
					} else {
						window.document.form_acc.suspicion.value = 'Use for multi closings only';
						window.document.form_acc.suspicion.disabled = true;
					}
					window.document.form_acc.choise[0].checked = true;
				}
			</script>
			<tr>
				<td align="right" valign="top" class="bold">Ban Points:</td>
				<td>
					<p>
						<input type="radio" name="choise" value="pre_select">
						Existing Reason: <select name="reason_pre_select" onchange="go()">
							<option value="0">[Please Select]</option><?php
							foreach($BanReasons as $ReasonID => $BanReason) { ?>
								<option value="<?php echo $ReasonID; ?>"<?php if($Disabled !== false && $ReasonID == $Disabled['ReasonID']) { ?> selected="selected"<?php } ?>><?php echo $BanReason; ?></option><?php
							} ?>
						</select>
					</p>
					<p>
						<input type="radio" name="choise" value="individual">
						New Reason: <input type="text" name="reason_msg" class="InputFields" style="width:400px;">
					</p>
					<p><input type="radio" name="choise" value="reopen"> Reopen! (Will remove ban points, if specified)</p>
					<p>Suspicion: <input type="text" name="suspicion" class="InputFields" disabled="disabled" style="width:300px;" value="Use for multi closings only"></p>
					<p>Ban Points: <input type="number" name="points" class="InputFields center" style="width:40px;"> points</p>
				</td>
			</tr>

			<tr>
				<td>&nbsp;</td>
				<td><hr noshade style="height:1px; border:1px solid white;"></td>
			</tr>

			<tr>
				<td class="right bold">Mail ban:</td>
				<td>
					Current mail ban: <?php
					if ($EditingAccount->isMailBanned()) { ?>
						<span class="red">For <?php echo format_time($EditingAccount->getMailBanned() - TIME); ?></span><?php
					} else { ?>
						<span class="green">None</span><?php
					} ?>
					<br /><br />
					<input type="radio" name="mailban" value="add_days" />
					Increase mail ban by <input type="number" name="mailban_days" class="InputFields center" style="width:40px" /> days
					<br />
					<input type="radio" name="mailban" value="remove" /> Remove mail ban
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
				<td><input type="radio" name="veteran_status" value="TRUE"<?php if($EditingAccount->isVeteranForced()) { ?> checked="checked"<?php } ?>>Yes</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="radio" name="veteran_status" value="FALSE"<?php if(!$EditingAccount->isVeteranForced()) { ?> checked="checked"<?php } ?>>No</td>
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
				<td><input type="text" name="player_name" class="InputFields" size="20"></td>
			</tr>
			<tr>
				<td align="right" class="bold">Game:</td>
				<td>
					<select name="game_id" size="1" class="InputFields">
						<option value="0">All Games</option><?php
						foreach ($Games as $Game) {
							?><option value="<?php echo $Game->getGameID(); ?>"><?php echo $Game->getDisplayName(); ?></option><?php
						} ?>
					</select>
				</td>
			</tr><?php
		} ?>

	</table>

	<br />
	<table>
		<tr>
			<td><?php
				if(isset($EditingAccount)) { ?>
					<input type="submit" name="action" value="Edit Account" class="InputFields" /><?php
				}
				else { ?>
					<input type="submit" name="action" value="Search" class="InputFields" /><?php
				} ?>
			</td><?php

			if(isset($EditingAccount)) { ?>
				<td>
					<div class="buttonA"><a class="buttonA" href="<?php echo $ResetFormHREF; ?>">Reset Form</a></div>
				</td><?php
			} ?>
	</table>
</form><?php

if(isset($ErrorMessage)) { ?>
	<div class="center red"><?php echo $ErrorMessage; ?></div><?php
}
if(isset($Message)) { ?>
	<div class="center"><?php echo $Message; ?></div><?php
} ?>
