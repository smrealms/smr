<div class="center">
	<a href="<?php echo WIKI_URL; ?>/game-guide/politics" target="_blank"><img style="float: right;" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Politics"/></a>
	<h3>President</h3><br/><?php
	$PresidentID = Council::getPresidentID($ThisPlayer->getGameID(), $RaceID);
	if ($PresidentID !== false) {
		$President = SmrPlayer::getPlayer($PresidentID, $ThisPlayer->getGameID()); ?>
		<table class="center standard" width="75%">
			<thead>
				<tr>
					<th>Name</th>
					<th>Race</th>
					<th>Alliance</th>
					<th>Experience</th>
				</tr>
			</thead>
			<tbody id="president" class="ajax">
				<tr>
					<td class="left">President <?php echo $President->getLinkedDisplayName(false); ?></td>
					<td><?php echo $ThisPlayer->getColouredRaceName($President->getRaceID(), true); ?></td>
					<td><?php echo $President->getAllianceDisplayName(true); ?></td>
					<td class="right"><?php echo number_format($President->getExperience()); ?></td>
				</tr>
			</tbody>
		</table><?php
	} else {
		?>This council doesn't have a president!<?php
	} ?>
	<br /><br />

	<img src="<?php echo Globals::getRaceImage($RaceID); ?>" width="212" height="270" /><br /><br />

	<h3>Council Members</h3><br /><?php
	$CouncilMembers = Council::getRaceCouncil($ThisPlayer->getGameID(), $RaceID);
	if (count($CouncilMembers) > 0) { ?>
		<table id="council-members" class="center standard" width="85%">
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th class="sort" data-sort="name">Name</th>
					<th>Race</th>
					<th class="sort" data-sort="alliance">Alliance</th>
					<th class="sort" data-sort="experience">Experience</th>
				</tr>
			</thead>
			<tbody class="list"><?php
				foreach ($CouncilMembers as $Ranking => $AccountID) {
					$CouncilPlayer = SmrPlayer::getPlayer($AccountID, $ThisPlayer->getGameID()); ?>
					<tr id="player-<?php echo $CouncilPlayer->getPlayerID(); ?>" class="ajax<?php if ($ThisPlayer->equals($CouncilPlayer)) { ?> bold<?php } ?>">
						<td><?php echo $Ranking; ?></td>
						<td class="left name" data-name="<?php echo $CouncilPlayer->getPlayerName(); ?>"><?php echo $CouncilPlayer->getLevelName(); ?> <?php echo $CouncilPlayer->getLinkedDisplayName(false); ?></td>
						<td><?php echo $ThisPlayer->getColouredRaceName($CouncilPlayer->getRaceID(), true); ?></td>
						<td class="alliance"><?php echo $CouncilPlayer->getAllianceDisplayName(true); ?></td>
						<td class="experience right"><?php echo number_format($CouncilPlayer->getExperience()); ?></td>
					</tr><?php
				} ?>
			</tbody>
		</table><?php
		$this->setListjsInclude('council_list');
	} else { ?>
		This council doesn't have any members!<?php
	} ?>
</div>
<br /><br />

<b>View Council For:</b><br /><?php
foreach (Globals::getRaces() as $RaceID => $RaceInfo) {
	if ($RaceID != RACE_NEUTRAL) { ?>
		<span class="smallFont"><?php
			echo $ThisPlayer->getColouredRaceName($RaceID, true); ?>
		</span><br /><?php
	}
} ?>
