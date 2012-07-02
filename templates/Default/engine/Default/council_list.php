
<div align="center" class="bold">President</div><?php
$President =& Council::getPresident($ThisPlayer->getGameID(),$RaceID);
if ($President !== false) { ?>
	<p>
		<table class="standard" align="center" width="75%">
			<tr>
				<th>Name</th>
				<th>Race</th>
				<th>Alliance</th>
				<th>Experience</th>
			</tr>
			<tr>
				<td valign="top">President <?php echo $President->getLinkedDisplayName(); ?></td>
				<td align="center"><?php echo $ThisPlayer->getColouredRaceName($President->getRaceID(), true); ?></td>
				<td><?php echo $President->getAllianceName(true); ?></td>
				<td align="right"><?php echo $President->getExperience(); ?></td>
			</tr>
		</table>
	</p><?php
}
else {
	?><div align="center">This council doesn't have a president!</div><?php
} ?>
<br /><br />

<div align="center" class="bold">Member</div><?php
$CouncilMembers = Council::getRaceCouncil($ThisPlayer->getGameID(), $RaceID);
if(count($CouncilMembers) > 0) { ?>
	<p>
		<table class="standard" align="center" width="85%">
			<tr>
				<th>&nbsp;</th>
				<th>Name</th>
				<th>Race</th>
				<th>Alliance</th>
				<th>Experience</th>
			</tr><?php

			foreach($CouncilMembers as $Ranking => $AccountID) {
				$CouncilPlayer =& SmrPlayer::getPlayer($AccountID, $ThisPlayer->getGameID()); ?>
				<tr<?php if ($ThisPlayer->equals($CouncilPlayer)) { ?> class="bold"<?php } ?>>
					<td align="center"><?php echo $Ranking; ?></td>
					<td valign="middle"><?php echo $CouncilPlayer->getLevelName(); ?> <?php echo $CouncilPlayer->getLinkedDisplayName(false); ?></td>
					<td align="center"><?php echo $ThisPlayer->getColouredRaceName($CouncilPlayer->getRaceID(), true); ?></td>
					<td><?php echo $CouncilPlayer->getAllianceName(true); ?></td>
					<td align="right"><?php echo $CouncilPlayer->getExperience(); ?></td>
				</tr><?php
			} ?>
		</table>
	</p><?php
}
else { ?>
	<div align="center">This council doesn't have any members!</div><?php
} ?>
<p>&nbsp;</p>

<b>View Council</b><br /><?php
$Races =& Globals::getRaces();
foreach($Races as $RaceID => $RaceInfo) {
	if($RaceID != RACE_NEUTRAL) { ?>
		<span style="font-size:75%;"><?php
			echo $ThisPlayer->getColouredRaceName($RaceID, true); ?>
		</span><br /><?php
	}
} ?>