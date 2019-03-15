All information on this page will be sent to the admin team.<br />
Be as accurate as possible with your bug description.
<p><i>Thank you for helping to improve the game!</i></p>

<form method="POST" action="<?php echo Globals::getBugReportProcessingHREF(); ?>">
	<table>
		<tr>
			<td class="bold">Login:</td>
			<td><?php echo $ThisAccount->getLogin(); ?></td>
		</tr>

		<tr>
			<td class="bold">Account ID:</td>
			<td><?php echo $ThisAccount->getAccountID(); ?></td>
		</tr>

		<tr>
			<td class="bold">Subject:</td>
			<td><input type="text" name="subject" class="InputFields" style="width:300px;"></td>
		</tr>

		<tr>
			<td class="bold" valign="top">Description:</td>
			<td><textarea spellcheck="true" class="InputFields" name="description" style="width:300px;height:100px;"></textarea></td>
		</tr>

		<tr>
			<td class="bold" valign="top">Steps to repeat:</td>
			<td><textarea spellcheck="true" class="InputFields" name="steps" style="width:300px;height:100px;"></textarea></td>
		</tr>

		<tr>
			<td class="bold" valign="top">Error Message:</td>
			<td><textarea spellcheck="true" class="InputFields" name="error_msg" style="width:300px;height:100px;"></textarea></td>
		</tr>

		<tr>
			<td></td>
			<td>
				<input type="submit" name="action" value="Submit" class="InputFields" />
			</td>
		</tr>

	</table>
</form>
