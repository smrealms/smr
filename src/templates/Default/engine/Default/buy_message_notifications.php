<?php declare(strict_types=1);

if (isset($Message)) {
	echo $Message; ?>
	<br /><br /><?php
}
?>

<span class="red">WARNING:</span> You will only receive message notifications after you log out or are inactive for <?php echo format_time(TIME_BEFORE_INACTIVE); ?>.<br />
Messages will be sent to your currently validated email, so make sure that is the email address to which you wish to receive emails.<br />
<br />
<?php
if (isset($MessageBoxes)) { ?>
	<table class="standard">
		<tr>
			<th>Message Type</th>
			<th>Messages Remaining</th>
			<th>&nbsp;</th>
		</tr><?php
		foreach ($MessageBoxes as $MessageBox) { ?>
			<tr>
				<td>
					<?php echo $MessageBox['Name']; ?>
				</td>
				<td class="center yellow"><?php echo $MessageBox['MessagesRemaining']; ?></td>
				<td>
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $MessageBox['BuyHref']; ?>">Buy <?php echo $MessageBox['MessagesPerCredit']; ?> Messages (1 SMR Credit)</a>
					</div>
				</td>
			</tr><?php
		} ?>
	</table><?php
}
