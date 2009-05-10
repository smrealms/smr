<form name="FORM" method="POST" action="<?php echo $VoteFormHREF; ?>">
	Question: <input name="question" type="text" class="InputFields" /><br />
	Days to end: <input name="days" type="text" class="InputFields" /><br />
	<input type="submit" name="action" value="Create" class="InputFields" /><br /><br />

	Vote: <select id="vote" name="vote"><?php 
		foreach($CurrentVotes as $CurrentVote)
		{
			?><option value="<?php echo $CurrentVote['ID'];?>"><?php echo $CurrentVote['Question'];?></option><?php
		} ?>
	</select><br />
	Option: <input name="option" type="text" class="InputFields" /><br />
	<input type="submit" name="action" value="Add Option" class="InputFields" />
</form>