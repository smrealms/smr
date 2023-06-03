<?php declare(strict_types=1);

use Smr\Pages\Player\Mission\AbandonProcessor;
use Smr\Pages\Player\Mission\AcceptProcessor;
use Smr\Pages\Player\Mission\ClaimProcessor;
use Smr\Pages\Player\Mission\DeclineProcessor;

/**
 * @var Smr\Player $ThisPlayer
 * @var array<int> $UnreadMissions
 */

if (isset($MissionMessage)) { ?>
	<span class="green">Mission Complete: </span><?php
	echo $MissionMessage;
}

foreach ($ThisPlayer->getAvailableMissions() as $MissionID => $Mission) { ?>
	<span class="green">New Mission: </span><?php
	echo bbify($Mission['Steps'][0]['Text']); ?>
	<div class="buttonA">
		<p>
			<a href="<?php echo (new AcceptProcessor($MissionID))->href(); ?>" class="buttonA">Accept</a>&nbsp;
			<a href="<?php echo (new DeclineProcessor($MissionID))->href(); ?>" class="buttonA">Decline</a>
		</p>
	</div><?php
}

foreach ($ThisPlayer->getActiveMissions() as $MissionID => $Mission) {
	if (in_array($MissionID, $UnreadMissions, true)) { ?>
		<span class="green">Task Complete: </span><?php
		echo bbify($Mission['Task']['Text']); ?><br /><?php
	}
	if ($Mission['Task']['Step'] === 'Claim') { ?>
		<div class="buttonA">
			<p><a href="<?php echo (new ClaimProcessor($MissionID))->href(); ?>" class="buttonA">Claim Reward</a></p>
		</div><?php
	} else { ?>
		<span class="green">Current Task: </span><?php
		echo bbify($Mission['Task']['Task']); ?><br/>
		<div class="buttonA">
			<p><a class="buttonA" href="<?php echo (new AbandonProcessor($MissionID))->href(); ?>">Abandon Mission</a></p>
		</div><?php
	}
}
