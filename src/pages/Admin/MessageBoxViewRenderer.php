<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class MessageBoxViewRenderer {

/**
 * @param array<int, array{ViewHREF: string, BoxName: string, TotalMessages: int}> $Boxes
 */
public static function renderBoxes(array $Boxes): void {
?>
	<table class="standard">
		<tr>
			<th>Folder</th>
			<th>Messages</th>
		</tr><?php
		foreach ($Boxes as $Box) { ?>
			<tr>
				<td><a href="<?php echo $Box['ViewHREF']; ?>"><?php echo $Box['BoxName']; ?></a></td>
				<td><?php echo $Box['TotalMessages']; ?></td>
			</tr><?php
		} ?>
	</table><?php
}

/**
 * @param array<int, array{ID: int, ReplyHREF?: string, SenderName: string, GameName: string, SendTime: string, Message: string}> $Messages
 */
public static function renderMessages(string $BackHREF, string $DeleteHREF, array $Messages): void {
?>
	<a href="<?php echo $BackHREF; ?>">&lt;&lt; Back</a><br /><br /><?php
	if (count($Messages) > 0) { ?>
		<form method="POST" action="<?php echo $DeleteHREF; ?>">
			<?php echo create_submit_display('Delete'); ?>
			&nbsp;
			<select name="action" size="1">
				<option>Marked Messages</option>
				<option>All Messages</option>
			</select>

			<p>Click the name to reply (requires admin messaging permission)</p>
			<table width="100%" class="standard"><?php
				foreach ($Messages as $Message) { ?>
					<tr>
						<td class="shrink">
							<input type="checkbox" name="message_id[]" value="<?php echo $Message['ID']; ?>">
						</td>
						<td class="noWrap">From: <?php
							if (isset($Message['ReplyHREF'])) {
								?><a href="<?php echo $Message['ReplyHREF']; ?>"><?php
							}
							echo $Message['SenderName'];
							if (isset($Message['ReplyHREF'])) {
								?></a><?php
							} ?>
						</td>
						<td><?php echo $Message['GameName']; ?></td>
					</tr>
					<tr>
						<td colspan="3">Sent at <?php echo $Message['SendTime']; ?></td>
					</tr>
					<tr>
						<td width="100%" colspan="3"><?php echo $Message['Message']; ?></td>
					</tr><?php
				} ?>
			</table>
		</form><?php
	} else {
		?>There are currently no messages in this box.<?php
	}
}

}
