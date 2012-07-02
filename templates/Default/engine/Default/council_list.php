<div class="center bold">President</div><br /><?php
$President =& Council::getPresident($ThisPlayer->getGameID(),$RaceID);
if ($President !== false) { ?>
	<table class="standard" align="center" width="75%">
		<tr>
			<th>Name</th>
			<th>Race</th>
			<th>Alliance</th>
			<th>Experience</th>
		</tr>
		<tr>
			<td>President <?php echo $President->getLinkedDisplayName(); ?></td>
			<td class="center"><?php echo $ThisPlayer->getColouredRaceName($President->getRaceID(), true); ?></td>
			<td><?php echo $President->getAllianceName(true); ?></td>
			<td class="right"><?php echo $President->getExperience(); ?></td>
		</tr>
	</table><?php
}
else {
	?><div align="center">This council doesn't have a president!</div><?php
} ?>
<br /><br />

<div class="center bold">Member</div><br /><?php
$CouncilMembers = Council::getRaceCouncil($ThisPlayer->getGameID(), $RaceID);
if(count($CouncilMembers) > 0) { ?>
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
				<td class="right"><?php echo $Ranking; ?></td>
				<td><?php echo $CouncilPlayer->getLevelName(); ?> <?php echo $CouncilPlayer->getLinkedDisplayName(false); ?></td>
				<td class="center"><?php echo $ThisPlayer->getColouredRaceName($CouncilPlayer->getRaceID(), true); ?></td>
				<td><?php echo $CouncilPlayer->getAllianceName(true); ?></td>
				<td class="right"><?php echo $CouncilPlayer->getExperience(); ?></td>
			</tr><?php
		} ?>
	</table><?php
}
else { ?>
	<div align="center">This council doesn't have any members!</div><?php
} ?>
<br /><br />

<b>View Council</b><br /><?php
$Races =& Globals::getRaces();
foreach($Races as $RaceID => $RaceInfo) {
	if($RaceID != RACE_NEUTRAL) { ?>
		<span class="smallFont"><?php
			echo $ThisPlayer->getColouredRaceName($RaceID, true); ?>
		</span><br /><?php
	}
} ?>