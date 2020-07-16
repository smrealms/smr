<?php
if ($ThisShip->hasJump()) { ?>
	<br />
	<form class="standard" id="JumpDriveForm" method="POST" action="<?php echo $JumpDriveFormLink; ?>">
		<h2>Jump Drive</h2><br />
		<table cellspacing="0" cellpadding="0" class="nobord nohpad">
			<tr>
				<td>Jump To:&nbsp;</td>
				<td><input type="number" size="5" name="target" maxlength="5" class="center"></td>
				<td>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="action" value="Engage Jump (<?php echo TURNS_JUMP_MINIMUM; ?>+)"></td>
				<td>&nbsp;&nbsp;<input type="submit" name="action" value="Calculate Turn Cost" /></td>
			</tr>
		</table>
	</form><?php
} ?>
