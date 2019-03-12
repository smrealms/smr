<?php

if ($ShowRoles) { ?>
	<form class="standard" method="POST" action="<?php echo $SaveAllianceRolesHREF; ?>"><?php
} ?>


<div align="center">
	<div id="alliance-desc" class="ajax"><?php
		echo bbifyMessage($Alliance->getDescription()); ?>
	</div><?php
	if(isset($EditAllianceDescriptionHREF)) { ?>
		<br />
		<div class="buttonA"><a class="buttonA" href="<?php echo $EditAllianceDescriptionHREF; ?>">Edit</a></div>
		<br /><?php
	} ?>

	<br />

	<table class="standard inset">
		<tr>
			<th>Alliance Name</th>
			<th>Total Experience</th>
			<th>Average Experience</th>
			<th>Members</th>
		</tr>
		<tr id="alliance-info" class="ajax bold">
			<td><?php echo $Alliance->getAllianceName(); ?></td>
			<td class="center shrink"><?php echo number_format($AllianceExp); ?></td>
			<td class="center shrink"><?php echo number_format($AllianceAverageExp); ?></td>
			<td class="center shrink"><?php echo number_format($Alliance->getNumMembers()); ?></td>
		</tr>
	</table>
	<br />

	<h2>Current Members</h2><br />

	<table id="alliance-roster" class="standard fullwidth">
		<thead>
			<tr>
				<th class="shrink">&nbsp;</th>
				<th class="sort" data-sort="name">Trader Name</th>
				<th class="sort shrink" data-sort="race">Race</th>
				<th class="sort" data-sort="experience">Experience</th><?php
				if($ShowRoles) { ?>
					<th class="sort shrink" data-sort="role">Role</th><?php
				}
				if(isset($ActiveIDs)) { ?>
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
					$Class.= ' newbie';
				} ?>
				<tr id="player-<?php echo $AlliancePlayer->getPlayerID(); ?>" class="ajax<?php echo $Class; ?>">
					<td class="center"><?php
						if ($AlliancePlayer->getAccountID() == $Alliance->getLeaderID()) { ?>*<?php }
						echo $Count++; ?>
					</td>
					<td class="name"><?php
						echo $AlliancePlayer->getLevelName(); ?>&nbsp;<?php echo $AlliancePlayer->getLinkedDisplayName(false); ?>
					</td>
					<td class="center race"><?php
						echo $ThisPlayer->getColouredRaceName($AlliancePlayer->getRaceID()); ?>
					</td>
					<td class="center experience"><?php
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
					if($ThisPlayer->getAllianceID() == $Alliance->getAllianceID()) { ?>
						<td class="center status"><?php
							if(in_array($AlliancePlayer->getAccountID(), $ActiveIDs)) { ?>
								<span class="green">Online</span><?php
							}
							else if($ThisPlayer->getAccountID() == $Alliance->getLeaderID() && $Disabled = SmrAccount::getAccount($AlliancePlayer->getAccountID())->isDisabled()) { ?>
								<span class="red">Banned Until:<br/><?php echo date(DATE_FULL_SHORT_SPLIT,$Disabled['Time']); ?></span><?php
							}
							else { ?>
								<span class="red">Offline</span><?php
							} ?>
						</td><?php
					} ?>
				</tr><?php
			} ?>
		</tbody>
	</table>
</div><?php

if ($Alliance->getAllianceID() == $ThisPlayer->getAllianceID()) { ?>
	<br /><h2>Options</h2><br /><?php
	if ($ShowRoles && $CanChangeRoles) { ?>
		<input class="submit" type="submit" name="action" value="Save Alliance Roles">&nbsp;&nbsp;<?php
	} ?>
	<div class="buttonA"><a class="buttonA" href="<?php echo $ToggleRolesHREF; ?>"><?php if ($ShowRoles) { ?>Hide Alliance Roles<?php } else { ?>Show Alliance Roles<?php } ?></a></div>
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
	echo $CanJoin;
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
<script>
var list = new List('alliance-roster', {
	valueNames: ['name', 'race', 'experience', 'role', 'status'],
	sortFunction: function(a, b, options) {
		return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g,''), b.values()[options.valueName].replace(/<.*?>|,/g,''), options);
	}
});
</script>
