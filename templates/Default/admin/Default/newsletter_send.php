<?php
if (!isset($NewsletterId)) { ?>
	No newsletters in the database!<?php
} else { ?>
	<h2>Send Newsletter</h2>
	<p>This sends the latest newsletter added to the DB: Newsletter #<?php echo $NewsletterId; ?></p>

	<p>Enter a recipient address (* for all players). Please send to yourself first to verify that
	everything displays as intended.</p>
	<?php echo $ProcessingForm; ?>
	<input type="text" name="to_email" value="<?php echo htmlspecialchars($CurrentEmail); ?>" id="InputFields" size="25">

	<p><?php echo create_submit('Send'); ?></p>
	</form>
	<br /><br />

	<h2>Newsletter #<?php echo $NewsletterId; ?> Preview</h2>
	<p>HTML body (will be displayed in most e-mail clients):</p>
	<table class="standard">
		<tr>
			<td><?php echo $NewsletterHtml; ?></td>
		</tr>
	</table>
  <br />

	<p>Plain text body (will only be displayed if HTML is empty or in e-mail clients that don't support HTML):</p>
	<table class="standard">
		<tr>
			<td><pre><?php echo $NewsletterText; ?></pre></td>
		</tr>
	</table>

<?php
}
?>
