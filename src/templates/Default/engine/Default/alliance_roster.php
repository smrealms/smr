<?php declare(strict_types=1);

use Smr\Alliance;

/**
 * @var Smr\Account $ThisAccount
 * @var Smr\Alliance $Alliance
 * @var Smr\Player $ThisPlayer
 * @var Smr\Template $this
 * @var bool $CanChangeRoles
 * @var ?string $SaveAllianceRolesHREF
 * @var ?array<int, string> $Roles
 * @var ?string $ToggleRolesHREF
 * @var int $AllianceExp
 * @var int $AllianceAverageExp
 * @var array<int, Smr\Player> $AlliancePlayers
 * @var string|bool $JoinRestriction
 * @var ?string $JoinHREF
 */

?>
<div class="center">
	<div id="alliance-desc" class="ajax"><?php
		echo bbify($Alliance->getDescription()); ?>
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
				<th class="sort" data-sort="sort_name">Trader Name</th>
				<th class="sort" data-sort="sort_race">Race</th>
				<th class="sort" data-sort="sort_experience">Experience</th><?php
				if (isset($Roles)) { ?>
					<th class="sort shrink" data-sort="sort_role">Role</th><?php
				}
				if (isset($ActiveIDs)) { ?>
					<th class="sort shrink" data-sort="sort_status">Status</th><?php
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
						if ($AlliancePlayer->isAllianceLeader()) { ?>*<?php }
						echo $Count++; ?>
					</td>
					<td class="sort_name left" data-name="<?php echo htmlentities($AlliancePlayer->getPlayerName()); ?>"><?php
						echo $AlliancePlayer->getLevelName(); ?>&nbsp;<?php echo $AlliancePlayer->getLinkedDisplayName(false); ?>
					</td>
					<td class="sort_race"><?php
						echo $ThisPlayer->getColouredRaceName($AlliancePlayer->getRaceID(), true); ?>
					</td>
					<td class="sort_experience"><?php
						echo number_format($AlliancePlayer->getExperience()); ?>
					</td><?php
					if (isset($Roles)) { ?>
						<td class="sort_role"><?php
							$PlayerRole = $AlliancePlayer->getAllianceRole();
							if ($CanChangeRoles && !$AlliancePlayer->isAllianceLeader()) { ?>
								<select form="roles" name="role[<?php echo $AlliancePlayer->getAccountID(); ?>]"><?php
									foreach ($Roles as $RoleID => $Role) { ?>
										<option value="<?php echo $RoleID; ?>"<?php
										if ($RoleID === $PlayerRole) { ?>
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
					if (isset($ActiveIDs)) { ?>
						<td class="sort_status center"><?php
							if (in_array($AlliancePlayer->getAccountID(), $ActiveIDs, true)) { ?>
								<span class="green">Online</span><?php
							} elseif ($ThisPlayer->isAllianceLeader() && $Disabled = $AlliancePlayer->getAccount()->isDisabled()) { ?>
								<span class="red">Banned Until:<br/><?php echo date($ThisAccount->getDateTimeFormatSplit(), $Disabled['Time']); ?></span><?php
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

$this->listjsInclude = 'alliance_roster';

if ($Alliance->getAllianceID() === $ThisPlayer->getAllianceID()) { ?>
	<br /><h2>Options</h2><br />
	<div class="buttonA">
		<a class="buttonA" href="<?php echo $ToggleRolesHREF; ?>"><?php
			if (isset($Roles)) { ?>Hide Alliance Roles<?php } else { ?>Show Alliance Roles<?php } ?>
		</a>
	</div><?php
	if (isset($Roles) && $CanChangeRoles) { ?>
		&nbsp;&nbsp;
		<form id="roles" style="display: inline;" method="POST" action="<?php echo $SaveAllianceRolesHREF; ?>">
			<input type="submit" name="action" value="Save Alliance Roles">
		</form><?php
	}
}

if ($JoinRestriction === false) { ?>
	<form class="standard" method="POST" action="<?php echo $JoinHREF; ?>"><?php
		if ($Alliance->getRecruitType() === Alliance::RECRUIT_OPEN) { ?>
			<p>This alliance is accepting all recruits!</p><?php
		} else { ?>
			<p>Enter password to join alliance</p>
			<input required name="password" size="30">&nbsp;<?php
		} ?>
		<input type="submit" name="action" value="Join">
	</form><?php
} elseif ($JoinRestriction !== true) { ?>
	<p><?php echo $JoinRestriction; ?></p><?php
}
