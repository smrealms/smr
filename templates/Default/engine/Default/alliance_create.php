<form method="POST" action="<?php echo $CreateHREF; ?>">
	<table class="standard">
		<tr>
			<td class="top">Name:</td>
			<td><input required type="text" name="name" size="30"></td>
		</tr>
		<tr>
			<td class="top">Description:</td>
			<td><textarea spellcheck="true" name="description"></textarea></td>
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
			<td>
				<select name="recruit_type" class="InputFields" onchange="togglePassword(this)"><?php
					foreach (SmrAlliance::allRecruitTypes() as $type => $text) { ?>
						<option value="<?php echo $type; ?>"><?php echo $text; ?></option><?php
					} ?>
				</select>
				<br />
				<div id="password-display">
					<input required id="password-input" name="password" placeholder=" Enter password here" size="30">
				</div>
			</td>
		</tr>
	</table>
	<br /><br />
	<input type="submit" name="action" value="Create" />
</form>
