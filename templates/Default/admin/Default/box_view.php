<?php
if(isset($Boxes)) { ?>
	<table class="standard">
		<tr>
			<th>Folder</th>
			<th>Messages</th>
		</tr><?php
		foreach($Boxes as $Box) { ?>
			<tr>
				<td><a href="<?php echo $Box['ViewHREF']; ?>"><?php echo $Box['BoxName']; ?></a></td>
				<td><?php echo $Box['TotalMessages']; ?></a></td>
			</tr><?php
		} ?>
	</table><?php
}
else { ?>
	<a href="<?php echo $BackHREF; ?>">Back</a><br /><?php
	if (isset($Messages)) { ?>
		<form method="POST" action="<?php echo $DeleteHREF; ?>">
			<input type="submit" name="action" value="Delete" class="InputFields" />
			&nbsp;
			<select name="action" size="1" class="InputFields">
				<option>Marked Messages</option>
				<option>All Messages</option>
			</select>

			<br /><br />
			Click the name to reply<br />
			<table width="100%" class="standard"><?php
				foreach($Messages as $Message) { ?>
					<tr>
						<td class="shrink">
							<input type="checkbox" name="message_id[]" value="<?php echo $Message['ID']; ?>">
						</td>
						<td class="noWrap">From: <?php
							if(isset($Message['ReplyHREF'])) {
								?><a href="<?php echo $Message['ReplyHREF']; ?>"><?php
							}
							echo $Message['SenderName'];
							if(isset($Message['ReplyHREF'])) {
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
	}
	else {
		?>There are currently no messages in this box.<?php
	}
} ?>
