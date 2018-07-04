<div class="center"><?php
	if ($WarRaces) { ?>
		We are at WAR with<br /><br /><?php
		foreach ($WarRaces as $RaceName) { ?>
			<span class="red">The <?php echo $RaceName; ?><br /></span><?php
		} ?>
		<br />The government will PAY for the destruction of their ships!
		<p>&nbsp;</p><?php
	}

	if ($AllBounties) { ?>
		<div class="center">Most wanted by the Federal Government</div><br /><?php
		$this->includeTemplate('includes/BountyList.inc', ['Bounties' => $AllBounties]);
	}
	if ($MyBounties) { ?>
		<div class="center">Claimable Bounties</div><br /><?php
		$this->includeTemplate('includes/BountyList.inc', ['Bounties' => $MyBounties]);
	}

	if (isset($JoinHREF)) { ?>
		<p><a href="<?php echo $JoinHREF; ?>" class="submitStyle">Become a deputy</a></p><?php
	} ?>
</div>
