<?php

$varAction = isset($var['action']) ? $var['action'] : '';
// Does anyone actually use these?
if ($ShowRoles) { ?>
	<form class="standard" method="POST')" action="<?php echo $SaveAllianceRolesHREF; ?>"><?php
} ?>


<div align="center"><?php
	echo bbifyMessage($Alliance->getDescription());
	if(isset($EditAllianceDescriptionHREF)) { ?>
		<br /><br />
		<div class="buttonA"><a class="buttonA" href="<?php echo $EditAllianceDescriptionHREF; ?>">&nbsp;Edit&nbsp;</a></div><?php
	} ?>

	<br /><br />

	<table class="standard inset">
		<tr>
			<th>Alliance Name</th>
			<th>Total Experience</th>
			<th>Average Experience</th>
			<th>Members</th>
		</tr>
		<tr class="bold">
			<td><?php echo $Alliance->getAllianceName(); ?></td>
			<td class="center shrink"><?php echo $AllianceExp; ?></td>
			<td class="center shrink"><?php echo $AllianceAverageExp; ?></td>
			<td class="center shrink"><?php echo $Alliance->getNumMembers(); ?></td>
		</tr>
	</table>
</div>
<br />

<h2>Current Members</h2><br />

<div align="center">
	<table class="standard fullwidth">
		<tr>
			<th class="shrink">&nbsp;</th>
			<th><a href="<?php echo $SortPlayerNameHREF; ?>">Trader Name</a></th>
			<th class="shrink"><a href="<?php echo $SortRaceNameHREF; ?>">Race</a></th>
			<th class="shrink"><a href="<?php echo $SortExperienceHREF; ?>">Experience</a></th><?php
			if($ShowRoles) { ?>
				<th class="shrink">Role</th><?php
			}
			if(isset($ActiveIDs)) { ?>
				<th class="shrink">Status</th><?php
			} ?>
		</tr><?php

		$Count = 1;
		foreach($AlliancePlayers as &$AlliancePlayer) {
			$Class = '';
			// check if this guy is the current guy
			if ($ThisPlayer->equals($AlliancePlayer)) {
				$Class .= 'bold';
			}
			if($AlliancePlayer->getAccount()->isNewbie()) {
				$Class.= ' newbie';
			}
			if($Class!='') {
				$Class = ' class="'.trim($Class).'"';
			} ?>
			<tr<?php echo $Class; ?>>

				<td class="center"><?php
					if ($AlliancePlayer->getAccountID() == $Alliance->getLeaderID()) { ?>*<?php }
					echo $Count++; ?>
				</td>
				<td><?php
					echo $AlliancePlayer->getLevelName(); ?>&nbsp;<?php echo $AlliancePlayer->getLinkedDisplayName(false); ?>
				</td>
				<td class="center"><?php
					echo $ThisPlayer->getColouredRaceName($AlliancePlayer->getRaceID()); ?>
				</td>
				<td class="center"><?php
					echo $AlliancePlayer->getExperience(); ?>
				</td><?php
				if ($ShowRoles) { ?>
					<td><?php
						$PlayerRole = $AlliancePlayer->getAllianceRole();
						if ($CanChangeRoles && $AlliancePlayer->getAccountID() != $Alliance->getLeaderID()) { ?>
							<select name="role[<?php echo $AlliancePlayer->getAccountID(); ?>]" id="InputFields"><?php
								foreach ($Roles as $RoleID => $Role) { ?>
									<option value="<?php echo $RoleID; ?>"<?php
									if ($RoleID == $PlayerRole) { ?>
										selected="selected"<?php
									} ?>><?php
										echo $Role; ?>
									</option><?php
								} ?>
							</select><?php
						} else {
							echo $Roles[$PlayerRole];
						} ?>
					</td><?php
				}

				if($ThisPlayer->getAllianceID() == $Alliance->getAllianceID()) {
					if(in_array($AlliancePlayer->getAccountID(), $ActiveIDs)) { ?>
						<td class="center green">Online</td><?php
					}
					else if($ThisPlayer->getAccountID() == $Alliance->getLeaderID() && $Disabled = SmrAccount::getAccount($AlliancePlayer->getAccountID())->isDisabled()) { ?>
						<td class="red"><p>Banned Until:<br/><?php echo date(DATE_FULL_SHORT_SPLIT,$Disabled['Time']); ?></p></td><?php
					}
					else { ?>
						<td class="center red">Offline</td><?php
					}
				} ?>
			</tr><?php
		} ?>
	</table>
</div><?php

if ($Alliance->getAllianceID() == $ThisPlayer->getAllianceID()) { ?>
	<br /><h2>Options</h2><br /><?php
	if ($ShowRoles) { ?>
		<input class="submit" type="submit" name="action" value="Save Alliance Roles">&nbsp;&nbsp;<?php
	} ?><div class="buttonA"><a class="buttonA" href="<?php echo $ToggleRolesHREF; ?>">&nbsp;<?php if ($ShowRoles) { ?>Hide Alliance Roles<?php } else { ?>Hide Alliance Roles<?php } ?>&nbsp;</a></div>
	</form><?php
}

if ($CanJoin === true) { ?>
	<br />
	<form class="standard" method="POST" action="<?php echo $JoinHREF; ?>">
		Enter password to join alliance<br /><br />
		<input type="password" name="password" size="30">&nbsp;<input class="submit" type="submit" name="action" value="Join">
	</form><?php
}
else if($CanJoin !== false) { ?>
	<br /><?php
	echo $canJoin;
}
?>