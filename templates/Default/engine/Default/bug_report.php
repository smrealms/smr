<span style="font-size:75%;">All information you can see on this page will be sent via email to the developer team!<br />
Be as accurate as possible with your bug description.</span>

<form method="POST" action="<?php echo Globals::getBugReportProcessingHREF(); ?>">
	<table>
		<tr>
			<td class="bold">Login:</td>
			<td><?php echo $ThisAccount->getLogin(); ?></td>
		</tr>

		<tr>
			<td class="bold">eMail:</td>
			<td><?php echo $ThisAccount->getEmail(); ?></td>
		</tr>

		<tr>
			<td class="bold">Account ID:</td>
			<td><?php echo $ThisAccount->getAccountID(); ?></td>
		</tr>

		<tr>
			<td class="bold">Subject:</td>
			<td><input type="text" name="subject" id="InputFields" style="width:300px;"></td>
		</tr>

		<tr>
			<td class="bold" valign="top">Description:</td>
			<td><textarea spellcheck="true" id="InputFields" name="description" style="width:300px;height:100px;"></textarea></td>
		</tr>

		<tr>
			<td class="bold" valign="top">Steps to repeat:</td>
			<td><textarea spellcheck="true" id="InputFields" name="steps" style="width:300px;height:100px;"></textarea></td>
		</tr>

		<tr>
			<td class="bold" valign="top">Error Message:</td>
			<td><textarea spellcheck="true" id="InputFields" name="error_msg" style="width:300px;height:100px;"></textarea></td>
		</tr>

		<tr>
			<td></td>
			<td>
				<input type="submit" name="action" value="Submit" id="InputFields" />
			</td>
		</tr>

	</table>
</form>