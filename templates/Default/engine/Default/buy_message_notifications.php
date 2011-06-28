<?php
if(isset($Message))
{
	echo $Message; ?>
	<br /><br /><?php
}
?>

<span class="red">WARNING:</span> Message notifications will only be received when you are logged out, therefore when logging out you will have to either have to click the logout link on the left or wait <?php echo format_time(SmrSession::TIME_BEFORE_EXPIRY); ?> for your session to time out.<br />
Messages will be sent to your currently validated email, so make sure that is the email address to which you wish to receive emails.<br />
<br />
<?php
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