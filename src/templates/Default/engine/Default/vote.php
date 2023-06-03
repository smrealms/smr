<?php declare(strict_types=1);

if (isset($Voting)) {
	?>Please take a couple of seconds to answer the following question(s) for the SMR Admin team. Thanks!<?php
	foreach ($Voting as $Vote) {
		?><br /><br />
		<form name="FORM" method="POST" action="<?php echo $Vote['HREF'] ?>">
			<span class="bold"><?php echo bbify($Vote['Question']); ?></span> <?php if (isset($Vote['TimeRemaining'])) { ?>(<?php echo $Vote['TimeRemaining']; ?> Remaining)<?php } else { ?>(Ended <?php echo $Vote['EndDate']; ?>)<?php } ?><br /><?php
			foreach ($Vote['Options'] as $VoteOption) { ?>
				<input type="radio" name="vote" <?php if (!isset($Vote['TimeRemaining'])) { ?>disabled="disabled" <?php } ?>value="<?php echo $VoteOption['ID']; ?>"<?php if ($VoteOption['Chosen']) { ?> checked<?php } ?>><?php echo bbify($VoteOption['Text']); ?> (<?php echo $VoteOption['Votes']; ?> votes)<br /><?php
			} ?>
			<?php if (isset($Vote['TimeRemaining'])) { ?><input type="submit" name="submit" value="Vote!"><br /><?php } ?><br />
		</form><?php
	} ?><br /><?php
}
