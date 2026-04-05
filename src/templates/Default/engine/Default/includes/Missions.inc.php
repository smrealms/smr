<?php declare(strict_types=1);

use Smr\Pages\Player\Mission\AbandonProcessor;
use Smr\Pages\Player\Mission\AcceptProcessor;
use Smr\Pages\Player\Mission\ClaimProcessor;
use Smr\Pages\Player\Mission\DeclineProcessor;

/**
 * @var Smr\Player $ThisPlayer
 * @var array<int, ?string> $UnreadMissions
 */

if (isset($MissionMessage)) { ?>
	<span class="green">Mission Complete: </span><?php
	echo $MissionMessage;
}

foreach ($ThisPlayer->getAvailableMissions() as $Mission) { ?>
	<span class="green">New Mission: </span><?php
	echo bbify($Mission->getFirstMessage()); ?>
	<div class="buttonA">
		<p>
			<a href="<?php echo (new AcceptProcessor($Mission))->href(); ?>" class="buttonA">Accept</a>&nbsp;
			<a href="<?php echo (new DeclineProcessor($Mission))->href(); ?>" class="buttonA">Decline</a>
		</p>
	</div><?php
}

foreach ($ThisPlayer->getActiveMissionStates() as $MissionID => $MissionState) {
	$UnreadMessage = $UnreadMissions[$MissionID];
	if ($UnreadMessage !== null) { ?>
		<span class="green">Task Complete: </span><?php
		echo bbify($UnreadMessage); ?><br /><?php
	} ?>
	<span class="green">Current Task: </span><?php
	echo bbify($MissionState->getTask()); ?><br/>
	<div class="buttonA">
		<p><a class="buttonA" href="<?php echo (new AbandonProcessor($MissionState))->href(); ?>">Abandon Mission</a></p>
	</div><?php
	if ($MissionState->hasClaimableReward($ThisPlayer->getSectorID())) { ?>
		<div class="buttonA">
			<p><a href="<?php echo (new ClaimProcessor($MissionState->mission))->href(); ?>" class="buttonA">Claim Reward</a></p>
		</div><?php
	}
}
