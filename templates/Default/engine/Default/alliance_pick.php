<table class="standard">
	<tr>
		<th>Leader</th>
		<th>Alliance</th>
		<th>Members</th>
		<th>Pick</th>
	</tr><?php
	foreach ($Teams as &$Team) {
		// boldface this row if it is the current player's alliance
		$Class = ($Team['Leader']->getPlayerID() == $PlayerID) ? "bold" : ""; ?>
		<tr class="<?php echo $Class; ?>">
			<td><?php echo $Team['Leader']->getLinkedDisplayName(false); ?></td><?php
			// The leader may not have made an alliance yet
			if (isset($Team['Alliance'])) { ?>
				<td><?php echo $Team['Alliance']->getAllianceName(true); ?></td>
				<td class="center"><?php echo $Team['Alliance']->getNumMembers(); ?></td>
				<td class="center"><?php
					if ($Team['CanPick']) { ?>
						<span class="green">YES</span><?php
					} else { ?>
						<span class="red">NO</span><?php
					} ?>
				</td><?php
			} ?>
		</tr><?php
	} ?>
</table>
<br />

<?php 
if ($CanPick) { ?>
	<p>You may pick a new member now!</p><?php
} else { ?>
	<p>You may not pick until another team picks!<p><?php
}

if(count($PickPlayers)>0) { ?>
	<table class="standard">
		<tr>
			<th>Action</th>
			<th>Player Name</th>
			<th>Race Name</th>
			<th>HoF Name</th>
			<th>User Score</th>
		</tr><?php
		foreach($PickPlayers as &$PickPlayer) { ?>
			<tr>
				<td><?php
				if ($CanPick) { ?>
					<div>
						<form id="PlayerPickForm" action="<?php echo $PickPlayer['HREF']; ?>" method="POST">
							<input type="submit" value="Pick"/>
						</form>
					</div><?php
				} ?>
				</td>
				<td>
					<?php echo $PickPlayer['Player']->getPlayerName(); ?>
				</td>
				<td>
					<?php echo $PickPlayer['Player']->getRaceName(); ?>
				</td>
				<td>
					<?php echo $PickPlayer['Player']->getAccount()->getHofName(true); ?>
				</td>
				<td>
					<?php echo $PickPlayer['Player']->getAccount()->getScore(); ?>
				</td>
			</tr><?php
		} ?>
	</table><?php
}
else {
	?>No one left to pick.<?php
} ?>

<br /><br />
<h2>Draft History</h2>
<?php
if (count($History) > 0) { ?>
	<table class="standard">
		<tr>
			<th>Pick</th>
			<th>Leader</th>
			<th>Date Picked</th>
			<th>Player Name</th>
			<th>Race Name</th>
			<th>HoF Name</th>
			<th>User Score</th>
		</tr><?php
		foreach(array_reverse($History, true) as $i => &$Pick) { ?>
			<tr>
				<td class="center"><?php echo $i+1; ?></td>
				<td><?php echo $Pick['Leader']->getPlayerName(); ?></td>
				<td><?php echo date(DATE_FULL_SHORT, $Pick['Time']); ?></td>
				<td><?php echo $Pick['Player']->getPlayerName(); ?></td>
				<td><?php echo $Pick['Player']->getRaceName(); ?></td>
				<td><?php echo $Pick['Player']->getAccount()->getHofName(true); ?></td>
				<td><?php echo $Pick['Player']->getAccount()->getScore(); ?></td>
			</tr><?php
		} ?>
	</table><?php
} else { ?>
	No picks have been made yet.<?php
}

?>
