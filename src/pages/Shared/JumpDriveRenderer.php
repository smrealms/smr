<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\AbstractShip;
use Smr\Pages\Player\SectorJumpProcessor;

class JumpDriveRenderer {

public static function render(
	AbstractShip $ThisShip,
	?SectorJumpProcessor $JumpDrivePage,
): void {
if ($ThisShip->hasJump()) {
	assert($JumpDrivePage !== null); ?>
	<br />
	<form class="standard" id="JumpDriveForm" method="POST" action="<?php echo $JumpDrivePage->href(); ?>">
		<h2>Jump Drive</h2><br />
		<table cellspacing="0" cellpadding="0" class="nobord nohpad">
			<tr>
				<td>Jump To:&nbsp;</td>
				<td><input type="number" size="5" name="target" maxlength="5" class="center"></td>
				<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo create_submit_display('Engage Jump (' . TURNS_JUMP_MINIMUM . '+)'); ?></td>
				<td>&nbsp;&nbsp;<?php echo $JumpDrivePage->actionCalculate->html('Calculate Turn Cost'); ?></td>
			</tr>
		</table>
	</form><?php
}

}

}
