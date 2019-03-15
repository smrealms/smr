
<p>Please use this form to send your feedback or
questions to the admin team of Space Merchant Realms!</p>

<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	<table>
		<tr>
			<td class="bold">From:</td>
			<td><?php echo $From; ?></td>
		</tr>

		<tr>
			<td class="bold">To:</td>
			<td>
				<select name="receiver">
					<option default>support@smrealms.de</option>
					<option>multi@smrealms.de</option>
					<option>beta@smrealms.de</option>
					<option>chat@smrealms.de</option>
				</select>
			</td>
		</tr>

		<tr>
			<td class="bold">Subject:</td>
			<td><input type="text" name="subject" class="InputFields" style="width:500px;"></td>
		</tr>

		<tr>
			<td class="bold" valign="top">Message:</td>
			<td><textarea spellcheck="true" class="InputFields" name="msg" style="width:500px;height:300px;"></textarea></td>
		</tr>

		<tr>
			<td></td>
			<td>
				<input type="submit" name="action" value="Submit" class="InputFields" />
			</td>
		</tr>

	</table>
</form>
