<form name="form_acc" method="POST" action="<?php echo $SearchHREF; ?>">
	<table cellpadding="3" border="0">
		<tr>
			<td class="right bold">Account ID:</td>
			<td><input type="number" name="account_id" size="5"></td>
		</tr>
		<tr>
			<td class="right bold">Login:</td>
			<td><input type="text" name="login" size="20"></td>
		</tr>
		<tr>
			<td class="right bold">Validation Code:</td>
			<td><input type="text" name="val_code" size="20"></td>
		</tr>
		<tr>
			<td class="right bold">Email:</td>
			<td><input type="email" name="email" size="20"></td>
		</tr>
		<tr>
			<td class="right bold">HoF Name:</td>
			<td><input type="text" name="hofname" size="20"></td>
		</tr>

		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>

		<tr>
			<td class="right bold">Player Name:</td>
			<td><input type="text" name="player_name" size="20"></td>
		</tr>
		<tr>
			<td class="right bold">Game:</td>
			<td>
				<select name="game_id" size="1">
					<option value="0">All Games</option><?php
					foreach ($Games as $GameID => $GameName) { ?>
						<option value="<?php echo $GameID ?>"><?php echo $GameName; ?></option><?php
					} ?>
				</select>
			</td>
		</tr>

	</table>

	<br />
	<table>
		<tr>
			<td>
					<input type="submit" name="action" value="Search" />
			</td>
	</table>
</form><?php

if (isset($ErrorMessage)) { ?>
	<div class="center red"><?php echo $ErrorMessage; ?></div><?php
}
if (isset($Message)) { ?>
	<div class="center"><?php echo $Message; ?></div><?php
} ?>
