<form method="POST" action="<?php echo $CreateHREF; ?>">
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Name:</td>
			<td><input required type="text" name="name" size="30"></td>
		</tr>
		<tr>
			<td class="top">Description:</td>
			<td><textarea spellcheck="true" name="description"></textarea></td>
		</tr>
		<tr>
			<td class="top">Password:</td>
			<td><input required type="password" name="password" size="30"></td>
		</tr>
		<tr>
			<td class="top">Members start with:&nbsp;&nbsp;&nbsp;</td>
			<td>No permissions<input type="radio" name="Perms" value="none"><br />
				Basic permissions<input type="radio" name="Perms" value="basic" checked><br />
				Full permissions<input type="radio" name="Perms" value="full">
			</td>
		</tr>
		<tr>
			<td class="top">Recruiting:</td>
			<td>Yes<input type="radio" name="recruit" value="yes" checked><br />
				No<input type="radio" name="recruit" value="no">
			</td>
		</tr>
	</table>
	<br /><br />
	<input type="submit" name="action" value="Create" />
</form>
