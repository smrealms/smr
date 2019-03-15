<?php
if (count($Threads) > 0) { ?>
	<table id="topic-list" class="centered standard inset">
		<thead>
			<tr>
				<th class="sort" data-sort="topic">Topic</th>
				<th class="sort shrink" data-sort="author">Author</th>
				<th class="sort shrink" data-sort="replies">Replies</th>
				<th class="sort shrink" data-sort="lastReply">Last Reply</th>
			</tr>
		</thead>
		<tbody class="list"><?php
			foreach($Threads as $Thread) { ?>
				<tr id="topic-<?php echo $Thread['ThreadID']; ?>" class="ajax">
					<td class="topic"><?php
						if ($Thread['Unread']) {
							?><b><?php
						}
						?><a href="<?php echo $Thread['ViewHref']; ?>"><?php echo $Thread['Topic']; ?></a><?php
						if ($Thread['Unread']) {
							?></b><?php
						} ?>
					</td>
					<td class="author noWrap"><?php
						echo $Thread['Sender'];
						if($Thread['CanDelete']) {
							?><br /><small><a href="<?php echo $Thread['DeleteHref']; ?>">Delete Thread!</a></small><?php
						} ?>
					</td>
					<td class="replies center"><?php echo $Thread['Replies']; ?></td>
					<td class="lastReply noWrap" data-lastReply="<?php echo $Thread['SendTime']; ?>"><?php echo date(DATE_FULL_SHORT, $Thread['SendTime']); ?></td>
				</tr><?php
			} ?>
		</tbody>
	</table><br /><?php
}

if (isset($CreateNewThreadFormHref)) { ?>
	<h2>Create Thread</h2><br /><?php
	if(isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
	<form class="standard" id="CreateNewThreadForm" method="POST" action="<?php echo $CreateNewThreadFormHref; ?>">
	<table class="standardnobord nohpad">
		<tr>
			<td class="top">Topic:&nbsp;</td>
			<td class="mb"><input type="text" name="topic" size="30" value="<?php if(isset($Topic)) { echo htmlspecialchars($Topic); } ?>"></td>
			<td>For Alliance Eyes Only:<input class="InputFields" name="allEyesOnly" type="checkbox"<?php if(isset($AllianceEyesOnly) && $AllianceEyesOnly) { ?>checked="checked" <?php } ?>></td>
		</tr>
		<tr>
			<td class="top">Body:&nbsp;</td>
			<td colspan="2"><textarea spellcheck="true" name="body"><?php if(isset($Preview)) { echo $Preview; } ?></textarea></td>
		</tr>
	</table><br />
	<input class="submit" type="submit" name="action" value="New Thread">&nbsp;<input type="submit" name="action" value="Preview Thread" class="InputFields" />
	</form><?php
}
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
<script>
var list = new List('topic-list', {
	valueNames: ['topic', 'author', 'replies', {name: 'lastReply', attr: 'data-lastReply'}],
	sortFunction: function(a, b, options) {
		return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g,''), b.values()[options.valueName].replace(/<.*?>|,/g,''), options);
	}
});
</script>
