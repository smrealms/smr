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
						<input type="submit" value="Pick"/>
					</form>
				</div>
			</td>
			<td>
				<?php echo $PickPlayer['PickPlayer']->getPlayerName(); ?>
			</td>
			<td>
				<?php echo $PickPlayer['PickPlayer']->getRaceName(); ?>
			</td>
			<td>
				<?php echo $PickPlayer['PickPlayer']->getAccount()->getHofName(); ?>
			</td><?php
		} ?>
		</tr>
	</table><?php
}
else
{
	?>No one left to pick.<?php
} ?>