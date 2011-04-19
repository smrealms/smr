<?php

echo $Message;

if (isset($MessageBoxes))
{ ?>
	<table class="standard">
		<tr>
			<th>Message Type</th>
			<th>Messages Remaining</th>
			<th>&nbsp;</th>
		</tr><?php
		foreach ($MessageBoxes as $MessageBox)
		{ ?>
			<tr>
				<td>
					<?php echo $MessageBox['Name']; ?>
				</td>
				<td align="center" class="yellow"><?php echo $MessageBox['MessagesRemaining']; ?></td>
				<td>
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $MessageBox['BuyHref']; ?>">&nbsp;Buy <?php echo $MessageBox['MessagesPerCredit']; ?> Messages (1 SMR Credit)&nbsp;</a>
					</div>
				</td>
			</tr><?php
		} ?>
	</table><?php
}

?>