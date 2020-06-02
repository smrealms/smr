<?php

if ($ShowRoles && $CanChangeRoles) { ?>
	<form class="standard" method="POST" action="<?php echo $SaveAllianceRolesHREF; ?>"><?php
} ?>


<div class="center">
	<div id="alliance-desc" class="ajax"><?php
		echo bbifyMessage($Alliance->getDescription()); ?>
	</div><?php
	if (isset($EditAllianceDescriptionHREF)) { ?>
		<br />
		<div class="buttonA"><a class="buttonA" href="<?php echo $EditAllianceDescriptionHREF; ?>">Edit</a></div>
		<br /><?php
	} ?>

	<br />

	<table class="standard inset center">
		<tr>
			<th>Alliance Name</th>
			<th>Total Experience</th>
			<th>Average Experience</th>
			<th>Members</th>
		</tr>
		<tr id="alliance-info" class="ajax bold">
			<td class="left"><?php echo $Alliance->getAllianceDisplayName(); ?></td>
			<td class="shrink"><?php echo number_format($AllianceExp); ?></td>
			<td class="shrink"><?php echo number_format($AllianceAverageExp); ?></td>
			<td class="shrink"><?php echo number_format($Alliance->getNumMembers()); ?></td>
		</tr>
	</table>
	<br />

	<h2>Current Members</h2><br />

	<table id="alliance-roster" class="standard fullwidth center">
		<thead>
			<tr>
				<th class="shrink">&nbsp;</th>
				<th class="sort" data-sort="name">Trader Name</th>
				<th class="sort" data-sort="race">Race</th>
				<th class="sort" data-sort="experience">Experience</th><?php
				if ($ShowRoles) { ?>
					<th class="sort shrink" data-sort="role">Role</th><?php
				}
				if (isset($ActiveIDs)) { ?>
					<th class="sort shrink" data-sort="status">Status</th><?php
				} ?>
			</tr>
		</thead>
		<tbody class="list"><?php
			$Count = 1;
			foreach ($AlliancePlayers as $AlliancePlayer) {
				$Class = '';
				// check if this guy is the current guy
				if ($ThisPlayer->equals($AlliancePlayer)) {
					$Class .= ' bold';
				}
				if ($AlliancePlayer->hasNewbieStatus()) {
					$Class .= ' newbie';
				} ?>
				<tr id="player-<?php echo $AlliancePlayer->getPlayerID(); ?>" class="ajax<?php echo $Class; ?>">
					<td><?php
						if ($AlliancePlayer->getAccountID() == $Alliance->getLeaderID()) { ?>*<?php }
						echo $Count++; ?>
					</td>
					<td class="left name" data-name="<?php echo htmlentities($AlliancePlayer->getPlayerName()); ?>"><?php
						echo $AlliancePlayer->getLevelName(); ?>&nbsp;<?php echo $AlliancePlayer->getLinkedDisplayName(false); ?>
					</td>
					<td class="race"><?php
						echo $ThisPlayer->getColouredRaceName($AlliancePlayer->getRaceID(), true); ?>
					</td>
					<td class="experience"><?php
						echo number_format($AlliancePlayer->getExperience()); ?>
					</td><?php
					if ($ShowRoles) { ?>
						<td class="role"><?php
							$PlayerRole = $AlliancePlayer->getAllianceRole();
							if ($CanChangeRoles && $AlliancePlayer->getAccountID() != $Alliance->getLeaderID()) { ?>
								<select name="role[<?php echo $AlliancePlayer->getAccountID(); ?>]" class="InputFields"><?php
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
					if ($ThisPlayer->getAllianceID() == $Alliance->getAllianceID()) { ?>
						<td class="center status"><?php
							if (in_array($AlliancePlayer->getAccountID(), $ActiveIDs)) { ?>
								<span class="green">Online</span><?php
							} elseif ($ThisPlayer->getAccountID() == $Alliance->getLeaderID() && $Disabled = SmrAccount::getAccount($AlliancePlayer->getAccountID())->isDisabled()) { ?>
								<span class="red">Banned Until:<br/><?php echo date(DATE_FULL_SHORT_SPLIT, $Disabled['Time']); ?></span><?php
							} else { ?>
								<span class="red">Offline</span><?php
							} ?>
						</td><?php
					} ?>
				</tr><?php
			} ?>
		</tbody>
	</table>
</div><?php

$this->setListjsInclude('alliance_roster');

if ($Alliance->getAllianceID() == $ThisPlayer->getAllianceID()) { ?>
	<br /><h2>Options</h2><br />
	<div class="buttonA">
		<a class="buttonA" href="<?php echo $ToggleRolesHREF; ?>"><?php
			if ($ShowRoles) { ?>Hide Alliance Roles<?php } else { ?>Show Alliance Roles<?php } ?>
		</a>
	</div><?php
	if ($ShowRoles && $CanChangeRoles) { ?>
		&nbsp;&nbsp;
		<input class="submit" type="submit" name="action" value="Save Alliance Roles">
		</form><?php
	}
}

if ($CanJoin === true) { ?>
	<form class="standard" method="POST" action="<?php echo $JoinHREF; ?>"><?php
		if ($Alliance->getRecruitType() == SmrAlliance::RECRUIT_OPEN) { ?>
			<p>This alliance is accepting all recruits!</p>
			<input hidden name="password" value=""><?php
		} else { ?>
			<p>Enter password to join alliance</p>
			<input required name="password" size="30">&nbsp;<?php
		} ?>
		<input class="submit" type="submit" name="action" value="Join">
	</form><?php
} elseif ($CanJoin !== false) { ?>
	<p><?php echo $CanJoin; ?></p><?php
}
?>
