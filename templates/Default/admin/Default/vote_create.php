<?php if(isset($PreviewVote)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($PreviewVote); ?></td></tr></table><?php } ?>
<form name="VoteForm" method="POST" action="<?php echo $VoteFormHREF; ?>">
	Question: <input name="question" type="text" class="InputFields" value="<?php if(isset($PreviewVote)) { echo htmlspecialchars($PreviewVote); } ?>" /><br />
	Days to end: <input name="days" type="text" class="InputFields" value="<?php if(isset($Days)) { echo htmlspecialchars($Days); } ?>" /><br />
	<input type="submit" name="action" value="Create Vote" class="InputFields" />&nbsp;<input type="submit" name="action" value="Preview Vote" id="InputFields" /><br /><br />

	<?php if(isset($PreviewOption)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($PreviewOption); ?></td></tr></table><?php } ?>
	Vote: <select id="vote" name="vote"><?php
		foreach($CurrentVotes as $CurrentVote) {
			?><option value="<?php echo $CurrentVote['ID'];?>"<?php if(isset($VoteID)&&$CurrentVote['ID']==$VoteID) { ?>selected="selected"<?php } ?>><?php echo bbifyMessage($CurrentVote['Question']);?></option><?php
		} ?>
	</select><br />
	Option: <input name="option" type="text" class="InputFields" value="<?php if(isset($PreviewOption)) { echo htmlspecialchars($PreviewOption); } ?>" /><br />
	<input type="submit" name="action" value="Add Option" class="InputFields" />&nbsp;<input type="submit" name="action" value="Preview Option" id="InputFields" />
</form>