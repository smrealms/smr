<?php
/*
$AvailableMissions =& $ThisPlayer->getAvailableMissions();
if(count($AvailableMissions) > 0) {
	foreach($AvailableMissions as &$AvailableMission) {
		echo bbifyMessage($AvailableMission['Steps'][0]['Text']); ?><br/>
		<div class="buttonA"><a href="<?php echo Globals::getAcceptMissionHREF($AvailableMission); ?>" class="buttonA"> Accept </a></div><br/><?php
	}
}
*/

$Missions =& $ThisPlayer->getMissions();
if(count($Missions) > 0) {
	foreach($Missions as $MissionID => &$Mission) {
		if(in_array($MissionID, $UnreadMissions)) { ?>
			<span class="green">Task Complete: </span><?php
			echo bbifyMessage($Mission['Task']['Text']); ?><br/><?php
		} ?>
		<span class="green">Current Task: </span><?php
		echo bbifyMessage($Mission['Task']['Task']); ?><br/><?php
	}
}

?>