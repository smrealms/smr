<?php declare(strict_types=1);

namespace Smr\Pages\Account;

class VoteRenderer {

/**
 * @param array<int, array{ID: int, HREF: string, Question: string, Active: bool, EndInfo: string, Options: array<int, array{ID: int, Text: string, Chosen: bool, Votes: int}>}> $Voting
 */
public static function render(array $Voting): void {
if (count($Voting) > 0) {
	?>Please take a couple of seconds to answer the following question(s) for the SMR Admin team. Thanks!<?php
	foreach ($Voting as $Vote) {
		?><br /><br />
		<form name="FORM" method="POST" action="<?php echo $Vote['HREF'] ?>">
			<span class="bold"><?php echo bbify(htmlentities($Vote['Question'])); ?></span> <?php echo $Vote['EndInfo']; ?><br /><?php
			foreach ($Vote['Options'] as $VoteOption) { ?>
				<input type="radio" name="vote" <?php if (!$Vote['Active']) { ?>disabled="disabled" <?php } ?>value="<?php echo $VoteOption['ID']; ?>"<?php if ($VoteOption['Chosen']) { ?> checked<?php } ?>><?php echo bbify($VoteOption['Text']); ?> (<?php echo $VoteOption['Votes']; ?> votes)<br /><?php
			} ?>
			<?php if ($Vote['Active']) { echo create_submit_display('Vote!'); ?><br /><?php } ?><br />
		</form><?php
	} ?><br /><?php
}

}

}
