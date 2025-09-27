<?php declare(strict_types=1);

/**
 * @var ?int $Days
 */

if (isset($PreviewVote)) { ?><table class="standard"><tr><td><?php echo bbify(htmlentities($PreviewVote)); ?></td></tr></table><?php } ?>
<form name="VoteForm" method="POST" action="<?php echo $VoteFormHREF; ?>">
	Question: <input type="text" name="question" required value="<?php if (isset($PreviewVote)) { echo bbify(htmlentities($PreviewVote)); } ?>" /><br />
	Days to end: <input type="number" name="days" required value="<?php if (isset($Days)) { echo $Days; } ?>" /><br />
	<?php echo create_submit('action', 'Create Vote'); ?>&nbsp;<?php echo create_submit('action', 'Preview Vote'); ?>
</form>
<br /><br />

	<?php if (isset($PreviewOption)) { ?><table class="standard"><tr><td><?php echo bbify($PreviewOption); ?></td></tr></table><?php } ?>
<form name="VoteForm" method="POST" action="<?php echo $VoteFormHREF; ?>">
	Vote: <select id="vote" name="vote"><?php
		foreach ($CurrentVotes as $CurrentVote) {
			?><option value="<?php echo $CurrentVote['ID']; ?>"<?php if (isset($VoteID) && $CurrentVote['ID'] === $VoteID) { ?>selected="selected"<?php } ?>><?php echo bbify(htmlentities($CurrentVote['Question'])); ?></option><?php
		} ?>
	</select><br />
	Option: <input type="text" name="option" required value="<?php if (isset($PreviewOption)) { echo htmlspecialchars($PreviewOption); } ?>" /><br />
	<?php echo create_submit('action', 'Add Option'); ?>&nbsp;<?php echo create_submit('action', 'Preview Option'); ?>
</form>
