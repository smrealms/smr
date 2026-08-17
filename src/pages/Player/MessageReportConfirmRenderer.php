<?php declare(strict_types=1);

namespace Smr\Pages\Player;

class MessageReportConfirmRenderer {

	public static function render(string $MessageText, string $ConfirmHREF, string $CancelHREF): void {
		?>
		You have selected the following message:<br /><br />
		<table class="standard">
			<tr>
				<td><?php echo bbify($MessageText); ?></td>
			</tr>
		</table>

		<p>Are you sure you want to report this message to the admins?<br />
		<small><b>Please note:</b> Abuse of this system could end in disablement.<br />Therefore, please only notify if the message is inappropriate.</small></p>

		<?php echo create_submit_link($ConfirmHREF, 'Yes'); ?>
		&nbsp;&nbsp;
		<?php echo create_submit_link($CancelHREF, 'No');
	}

}
