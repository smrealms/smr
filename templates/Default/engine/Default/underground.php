<p>The location appears to be abandoned, until a group of
heavily-armed figures advance from the shadows.</p>
<p>&nbsp;</p>

<?php
if ($AllBounties) { ?>
	<div class="center">Most wanted by the Underground</div><br /><?php
	$this->includeTemplate('includes/BountyList.inc', ['Bounties' => $AllBounties]);
}
if ($MyBounties) { ?>
	<div class="center">Claimable Bounties</div><br /><?php
	$this->includeTemplate('includes/BountyList.inc', ['Bounties' => $MyBounties]);
}

if (isset($JoinHREF)) { ?>
	<p class="center">
		<a href="<?php echo $JoinHREF; ?>" class="submitStyle">Become a smuggler</a>
	</p><?php
} ?>
