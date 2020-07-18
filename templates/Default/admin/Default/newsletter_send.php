<?php
if (!isset($NewsletterId)) { ?>
	No newsletters in the database!<?php
} else { ?>
	<h2>Send Newsletter</h2>
	<p>This sends the latest newsletter added to the DB: Newsletter #<?php echo $NewsletterId; ?></p>

	<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	<p>Set the subject of the newsletter mail (the default is recommended).</p>
	<input type="text" name="subject" required value="<?php echo $DefaultSubject; ?>" size="35">

	<p>Choose a salutation for the newsletter (e.g. "Hello Players,"). If sending to<br />
	all players, specify only the greeting (e.g. "Hello") to prefix the account name.<br />
	If left empty, no salutation will be added.</p>
	<input type="text" name="salutation" value="Hello" size="35">

	<p>Enter a recipient address (* for all players).<br />
	Please send to yourself first to verify that everything displays as intended.</p>
	<input type="text" name="to_email" required value="<?php echo htmlspecialchars($CurrentEmail); ?>" size="35">

	<p><input type="submit" name="action" value="Send" /></p>
	</form>
	<br /><br />

	<h2>Newsletter #<?php echo $NewsletterId; ?> Preview</h2>
	<p>HTML body (will be displayed in most e-mail clients):</p>
	<table class="standard" width=75%>
		<tr>
			<td><?php echo $NewsletterHtml; ?></td>
		</tr>
	</table>
  <br />

	<p>Plain text body (will only be displayed if HTML is empty or in e-mail clients that don't support HTML):</p>
	<table class="standard" width=75%>
		<tr>
			<td><pre style="white-space:pre-wrap"><?php echo $NewsletterText; ?></pre></td>
		</tr>
	</table>

<?php
}
?>
