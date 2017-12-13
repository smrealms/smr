<h2>Alliance Operation Schedule</h2>
<?php
if (!empty($Message)) {
	echo "<p>$Message</p>";
}
if (!empty($OpDate)) { ?>
	<p>The next alliance operation is scheduled for:</p>
	<table class="nobord">
		<tr>
			<td><b>Date:</b></td>
			<td><?php echo $OpDate; ?></td>
		</tr>
		<tr>
			<td><b>Countdown:</b></td>
			<td><span id="countdown"><?php echo $OpCountdown; ?></span></td>
		</tr>
	</table>
	<br />
	<div class="buttonA"><a class="buttonA" href="<?php echo $OpProcessingHREF; ?>">&nbsp;Cancel&nbsp;</a></div>
	<?php
} else { ?>
	<p>Schedule the next alliance operation:<br><small>Enter the date in server time (example: Dec 12 18:30)</small></p>
	<form method="POST" action="<?php echo $OpProcessingHREF; ?>">
		<input type="text" name="date" />
		<input type="submit" value="Confirm" />
	</form><?
}
?>
