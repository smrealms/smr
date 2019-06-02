<?php
if (isset($MissionMessage)) { ?>
	<span class="green">Mission Complete: </span><?php
	echo $MissionMessage;
}

foreach ($ThisPlayer->getAvailableMissions() as $MissionID => $Mission) { ?>
	<span class="green">New Mission: </span><?php
	echo bbifyMessage($Mission['Steps'][0]['Text']); ?>
	<div class="buttonA">
		<p>
			<a href="<?php echo Mission::getAcceptHREF($MissionID); ?>" class="buttonA">Accept</a>&nbsp;
			<a href="<?php echo Mission::getDeclineHREF($MissionID); ?>" class="buttonA">Decline</a>
		</p>
	</div><?php
}

foreach ($ThisPlayer->getActiveMissions() as $MissionID => $Mission) {
	if (in_array($MissionID, $UnreadMissions)) { ?>
		<span class="green">Task Complete: </span><?php
		echo bbifyMessage($Mission['Task']['Text']); ?><br /><?php
	}
	if ($Mission['Task']['Step'] == 'Claim') { ?>
		<div class="buttonA">
			<p><a href="<?php echo Mission::getClaimRewardHREF($MissionID); ?>" class="buttonA">Claim Reward</a></p>
		</div><?php
	} else { ?>
		<span class="green">Current Task: </span><?php
		echo bbifyMessage($Mission['Task']['Task']); ?><br/>
		<div class="buttonA">
			<p><a class="buttonA" href="<?php echo Mission::getAbandonHREF($MissionID); ?>">Abandon Mission</a></p>
		</div><?php
	}
}

?>
