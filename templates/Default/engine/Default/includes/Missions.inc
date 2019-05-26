<?php
if(isset($MissionMessage)) { ?>
	<span class="green">Mission Complete: </span><?php
	echo $MissionMessage;
}

$AvailableMissions = $ThisPlayer->getAvailableMissions();
if(count($AvailableMissions) > 0) {
	foreach($AvailableMissions as $MissionID => $AvailableMission) { ?>
		<span class="green">New Mission: </span><?php
		echo bbifyMessage($AvailableMission['Steps'][0]['Text']); ?><br/><br/>
		<div class="buttonA">
			<a href="<?php echo Mission::getAcceptHREF($MissionID); ?>" class="buttonA">Accept</a>&nbsp;
			<a href="<?php echo Mission::getDeclineHREF($MissionID); ?>" class="buttonA">Decline</a>
		</div><br/><?php
	}
}


$Missions = $ThisPlayer->getActiveMissions();
if(count($Missions) > 0) {
	foreach($Missions as $MissionID => $Mission) {
		if(in_array($MissionID, $UnreadMissions)) { ?>
			<span class="green">Task Complete: </span><?php
			echo bbifyMessage($Mission['Task']['Text']); ?><br/><?php
		}
		if($Mission['Task']['Step'] == 'Claim') { ?>
			<span class="green">Claim Reward: </span>
			<div class="buttonA"><a href="<?php echo Mission::getClaimRewardHREF($MissionID); ?>" class="buttonA">Claim Reward</a></div><br/><?php
		}
		else { ?>
			<span class="green">Current Task: </span><?php
			echo bbifyMessage($Mission['Task']['Task']); ?><br/>
			<div class="buttonA">
				<p><a class="buttonA" href="<?php echo Mission::getAbandonHREF($MissionID); ?>">Abandon Mission</a></p>
			</div><?php
		}
	}
}

?>
