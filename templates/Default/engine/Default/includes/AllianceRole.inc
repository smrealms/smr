<form class="standard" id="RoleEditForm<?php echo $Role['RoleID']; ?>" method="POST" action="<?php echo $Role['HREF']; ?>">
	<table class="standard">
		<tr>
			<td>Name</td>
			<td><input type="text" name="role" value="<?php echo htmlspecialchars($Role['Name']); ?>" maxlength="32"<?php if (!$Role['EditingRole']){ ?> disabled="disabled"<?php } ?>>
		</tr><?php
		if ($Role['EditingRole']) { ?>
			<tr>
				<td rowspan="3">Withdrawal limit per 24 hours<br>(Or max negative balance for "positive balance")</td>
				<td><input type="number" name="maxWith" value="<?php echo max($Role['WithdrawalLimit'],0); ?>"></td>
			</tr>
			<tr>
				<td>Unlimited:<input type="checkbox" name="unlimited"<?php if ($Role['WithdrawalLimit'] == ALLIANCE_BANK_UNLIMITED){ ?> checked="checked"<?php } ?>></td>
			</tr>
			<tr>
				<td>Positive Balance:<input type="checkbox" name="positive" title="Members must deposit more than they withdraw"<?php if ($Role['PositiveBalance']){ ?> checked="checked"<?php } ?>></td>
			</tr><?php
			if (!$Role['TreatyCreated']) { ?>
				<tr>
					<td>Remove Member</td>
					<td><input type="checkbox" name="removeMember"<?php if ($Role['RemoveMember']){ ?> checked="checked"<?php } ?>></td>
				</tr>
				<tr>
					<td>Change Password</td>
					<td><input type="checkbox" name="changePW"<?php if ($Role['ChangePass']){ ?> checked="checked"<?php } ?>></td>
				</tr>
				<tr>
					<td>Change Message Of The Day</td>
					<td><input type="checkbox" name="changeMoD"<?php if ($Role['ChangeMod']){ ?> checked="checked"<?php } ?>></td>
				</tr>
				<tr>
					<td>Change Roles</td>
					<td><input type="checkbox" name="changeRoles"<?php if ($Role['ChangeRoles']){ ?> checked="checked"<?php } ?>></td>
				</tr>
				<tr>
					<td>Land On Planets</td>
					<td><input type="checkbox" name="planets"<?php if ($Role['PlanetAccess']){ ?> checked="checked"<?php } ?>></td>
				</tr>
				<tr>
					<td>Moderate Message Board</td>
					<td><input type="checkbox" name="mbMessages"<?php if ($Role['ModerateMessageboard']){ ?> checked="checked"<?php } ?>></td>
				</tr>
				<tr>
					<td>Exempt Withdrawals</td>
					<td><input type="checkbox" name="exemptWithdrawals" title="This user can mark withdrawals from the alliance account as 'for the alliance' instead of 'for the individual'"<?php if ($Role['ExemptWithdrawals']){ ?> checked="checked"<?php } ?>></td>
				</tr>
				<tr>
					<td>Send Alliance Message</td>
					<td><input type="checkbox" name="sendAllMsg"<?php if ($Role['SendAllianceMessage']){ ?> checked="checked"<?php } ?>></td>
				</tr>
				<tr>
					<td>Operations Leader</td>
					<td><input type="checkbox" name="opLeader" title="Can schedule operations and designate the flagship" <?php if ($Role['OpLeader']){ ?> checked="checked"<?php } ?> /></td>
				</tr>
				<tr>
					<td>View Bonds In Planet List</td>
					<td><input type="checkbox" name="viewBonds"<?php if ($Role['ViewBondsInPlanetList']){ ?> checked="checked"<?php } ?>></td>
				</tr><?php
			}
		} ?>
		<tr>
			<td colspan="2" class="center"><input class="submit" type="submit" name="action" value="<?php if ($Role['CreatingRole']) { ?>Create<?php } else if($Role['EditingRole']){ ?>Submit Changes<?php }else{ ?>Edit<?php } ?>"></td>
		</tr>
	</table>
</form><br />
