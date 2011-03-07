<?php 
if(count($PickPlayers)>0)
{ ?>
	<table class="standard">
		<tr>
			<th></th>
			<th>Player Name</th>
			<th>Race Name</th>
			<th>HoF Name</th>
		</tr>
		<tr><?php
		foreach($PickPlayers as &$PickPlayer)
		{ ?>
			<td>
				<div>
					<form id="PlayerPickForm" action="<?php echo $PickPlayer['PlayerPickHREF']; ?>" method="POST">
						<input name="picked_account_id" type="hidden" value="<?php echo $PickPlayer['PickPlayer']->getAccountID(); ?>"/>
					</form>
				</div>
			</td>
			<td>
				<?php echo $PickPlayer->getPlayerName(); ?>
			</td>
			<td>
				<?php echo $PickPlayer->getRaceName(); ?>
			</td>
			<td>
				<?php echo $PickPlayer->getAccount()->getHofName(); ?>
			</td><?php
		} ?>
		</tr>
	</table><?php
}
else
{
	?>No one left to pick.<?php
} ?>