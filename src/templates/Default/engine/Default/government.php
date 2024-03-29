<?php declare(strict_types=1);

/**
 * @var array<Smr\Bounty> $AllBounties
 * @var array<Smr\Bounty> $MyBounties
 * @var Smr\Template $this
 * @var array<string> $WarRaces
 */

?>
<div class="center"><?php
	if (count($WarRaces) > 0) { ?>
		We are at WAR with<br /><br /><?php
		foreach ($WarRaces as $RaceName) { ?>
			<span class="red">The <?php echo $RaceName; ?><br /></span><?php
		} ?>
		<br />The government will PAY for the destruction of their ships!
		<p>&nbsp;</p><?php
	}

	if (count($AllBounties) > 0) { ?>
		<div class="center">Most wanted by the Federal Government</div><br /><?php
		$this->includeTemplate('includes/BountyList.inc.php', ['Bounties' => $AllBounties]);
	}
	if (count($MyBounties) > 0) { ?>
		<div class="center">Claimable Bounties</div><br /><?php
		$this->includeTemplate('includes/BountyList.inc.php', ['Bounties' => $MyBounties]);
	}

	if (isset($JoinHREF)) { ?>
		<p><a href="<?php echo $JoinHREF; ?>" class="submitStyle">Become a deputy</a></p><?php
	} ?>
</div>
