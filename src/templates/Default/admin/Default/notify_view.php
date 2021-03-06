<?php
if (empty($Messages)) { ?>
	<p>There are no reported Messages.</p><?php
	return;
} ?>

<p>Click either name to reply (requires admin messaging permission)</p>
<form method="POST" action="<?php echo $DeleteHREF; ?>">
	<table width="100%" class="standard"><?php
	foreach ($Messages as $Message) { ?>
		<tr>
			<td rowspan="2" class="top shrink">
				<input type="checkbox" name="notify_id[]" value="<?php echo $Message['notifyID']; ?>">
			</td>
			<td class="noWrap">
				<span class="yellow smallCaps">From: </span><?php echo $Message['senderName']; ?><br />
				<span class="yellow smallCaps">To: </span><?php echo $Message['receiverName']; ?>
			</td>
			<td class="noWrap">
				Sent at <?php echo $Message['sentDate']; ?><br />
				Reported at <?php echo $Message['reportDate']; ?>
			</td>
			<td><?php echo $Message['gameName']; ?></td>
		</tr>
		<tr>
			<td colspan="3"><?php echo $Message['text']; ?></td>
		</tr><?php
	} ?>
	</table>
	<br />
	<input type="submit" name="action" value="Delete" />
</form>
