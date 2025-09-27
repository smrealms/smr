<?php declare(strict_types=1);

use Smr\Alliance;

/**
 * @var string $CreateHREF
 */

?>
<form method="POST" action="<?php echo $CreateHREF; ?>">
	<table class="standard">
		<tr>
			<td class="top">Name:</td>
			<td><input required type="text" name="name" maxlength="<?php echo Alliance::MAXLENGTH_NAME; ?>" size="30"></td>
		</tr>
		<tr>
			<td class="top">Description:</td>
			<td><textarea spellcheck="true" name="description" maxlength="<?php echo Alliance::MAXLENGTH_DESCRIPTION; ?>"></textarea></td>
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
				<select name="recruit_type" onchange="togglePassword(this)"><?php
					foreach (Alliance::allRecruitTypes() as $type => $text) { ?>
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
	<?php echo create_submit('action', 'Create'); ?>
</form>
