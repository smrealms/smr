<div align="center">
	<a href="<?php echo WIKI_URL; ?>/game-guide/politics" target="_blank"><img align="right" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Politics"/></a>
	<h3>President</h3><br/><?php
	$President =& Council::getPresident($ThisPlayer->getGameID(),$RaceID);
	if ($President !== false) { ?>
		<table class="standard" width="75%">
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
					<td>President <?php echo $President->getLinkedDisplayName(false); ?></td>
					<td class="center"><?php echo $ThisPlayer->getColouredRaceName($President->getRaceID(), true); ?></td>
					<td><?php echo $President->getAllianceName(true); ?></td>
					<td class="right"><?php echo number_format($President->getExperience()); ?></td>
				</tr>
			</tbody>
		</table><?php
	}
	else {
		?>This council doesn't have a president!<?php
	} ?>
	<br /><br />

	<img src="<?php echo Globals::getRaceImage($RaceID); ?>" width="212" height="270" /><br /><br />

	<h3>Council Members</h3><br /><?php
	$CouncilMembers = Council::getRaceCouncil($ThisPlayer->getGameID(), $RaceID);
	if(count($CouncilMembers) > 0) { ?>
		<table id="council-members" class="standard" width="85%">
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
				foreach($CouncilMembers as $Ranking => $AccountID) {
					$CouncilPlayer =& SmrPlayer::getPlayer($AccountID, $ThisPlayer->getGameID()); ?>
					<tr id="player-<?php echo $CouncilPlayer->getPlayerID(); ?>" class="ajax<?php if ($ThisPlayer->equals($CouncilPlayer)) { ?> bold<?php } ?>">
						<td class="right"><?php echo $Ranking; ?></td>
						<td class="name"><?php echo $CouncilPlayer->getLevelName(); ?> <?php echo $CouncilPlayer->getLinkedDisplayName(false); ?></td>
						<td class="center"><?php echo $ThisPlayer->getColouredRaceName($CouncilPlayer->getRaceID(), true); ?></td>
						<td class="alliance"><?php echo $CouncilPlayer->getAllianceName(true); ?></td>
						<td class="experience right"><?php echo number_format($CouncilPlayer->getExperience()); ?></td>
					</tr><?php
				} ?>
			</tbody>
		</table><?php
	}
	else { ?>
		This council doesn't have any members!<?php
	} ?>
</div>
<br /><br />

<b>View Council For:</b><br /><?php
$Races =& Globals::getRaces();
foreach($Races as $RaceID => $RaceInfo) {
	if($RaceID != RACE_NEUTRAL) { ?>
		<span class="smallFont"><?php
			echo $ThisPlayer->getColouredRaceName($RaceID, true); ?>
		</span><br /><?php
	}
} ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
<script>
var list = new List('council-members', {
	valueNames: ['name', 'race', 'alliance', 'experience'],
	sortFunction: function(a, b, options) {
		var regex;
		if(options.valueName === 'name') {
			regex = /^.*?<.*?>|<.*?>/g;
		}
		else {
			regex = /,/g;
		}
		return list.utils.naturalSort(a.values()[options.valueName].replace(regex,''), b.values()[options.valueName].replace(regex,''), options);
	}
});
</script>
