<?php
if (count($Threads) > 0) { ?>
	<table id="topic-list" class="centered standard inset">
		<thead>
			<tr>
				<th class="sort" data-sort="sort_topic">Topic</th>
				<th class="sort shrink" data-sort="sort_author">Author</th>
				<th class="sort shrink" data-sort="sort_replies">Replies</th>
				<th class="sort shrink" data-sort="sort_lastReply">Last Reply</th>
			</tr>
		</thead>
		<tbody class="list"><?php
			foreach ($Threads as $Thread) { ?>
				<tr id="topic-<?php echo $Thread['ThreadID']; ?>" class="ajax">
					<td class="sort_topic"><?php
						if ($Thread['Unread']) {
							?><b><?php
						}
						?><a href="<?php echo $Thread['ViewHref']; ?>"><?php echo $Thread['Topic']; ?></a><?php
						if ($Thread['Unread']) {
							?></b><?php
						} ?>
					</td>
					<td class="sort_author noWrap"><?php
						echo $Thread['Sender'];
						if ($Thread['CanDelete']) {
							?><br /><small><a href="<?php echo $Thread['DeleteHref']; ?>">Delete Thread!</a></small><?php
						} ?>
					</td>
					<td class="sort_replies center"><?php echo $Thread['Replies']; ?></td>
					<td class="sort_lastReply noWrap" data-lastReply="<?php echo $Thread['SendTime']; ?>"><?php echo date(DATE_FULL_SHORT, $Thread['SendTime']); ?></td>
				</tr><?php
			} ?>
		</tbody>
	</table><br /><?php
	$this->setListjsInclude('alliance_message');
}

if (isset($CreateNewThreadFormHref)) { ?>
	<h2>Create Thread</h2><br /><?php
	if (isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
	<form class="standard" id="CreateNewThreadForm" method="POST" action="<?php echo $CreateNewThreadFormHref; ?>">
	<table class="standardnobord nohpad">
		<tr>
			<td class="top">Topic:&nbsp;</td>
			<td class="mb"><input type="text" name="topic" required size="30" value="<?php if (isset($Topic)) { echo htmlspecialchars($Topic); } ?>"></td>
			<td>For Alliance Eyes Only:<input name="allEyesOnly" type="checkbox"<?php if (isset($AllianceEyesOnly) && $AllianceEyesOnly) { ?>checked="checked" <?php } ?>></td>
		</tr>
		<tr>
			<td class="top">Body:&nbsp;</td>
			<td colspan="2"><textarea spellcheck="true" name="body" required><?php if (isset($Preview)) { echo $Preview; } ?></textarea></td>
		</tr>
	</table><br />
	<input type="submit" name="action" value="New Thread">&nbsp;<input type="submit" name="action" value="Preview Thread" />
	</form><?php
}
?>
