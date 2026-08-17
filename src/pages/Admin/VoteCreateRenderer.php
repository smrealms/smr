<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class VoteCreateRenderer {

/**
 * @param array<int, array{ID: int, Question: string}> $CurrentVotes
 */
public static function render(VoteCreateProcessor $VoteFormPage, array $CurrentVotes, ?string $PreviewVote, ?int $Days, ?string $PreviewOption, ?int $VoteID): void {
if (isset($PreviewVote)) { ?><table class="standard"><tr><td><?php echo bbify(htmlentities($PreviewVote)); ?></td></tr></table><?php } ?>
<form name="VoteForm" method="POST" action="<?php echo $VoteFormPage->href(); ?>">
	Question: <input type="text" name="question" required value="<?php if (isset($PreviewVote)) { echo bbify(htmlentities($PreviewVote)); } ?>" /><br />
	Days to end: <input type="number" name="days" required value="<?php if (isset($Days)) { echo $Days; } ?>" /><br />
	<?php echo $VoteFormPage->actionCreateVote->html(); ?>&nbsp;<?php echo $VoteFormPage->actionPreviewVote->html(); ?>
</form>
<br /><br />

	<?php if (isset($PreviewOption)) { ?><table class="standard"><tr><td><?php echo bbify($PreviewOption); ?></td></tr></table><?php } ?>
<form name="VoteForm" method="POST" action="<?php echo $VoteFormPage->href(); ?>">
	Vote: <select id="vote" name="vote"><?php
		foreach ($CurrentVotes as $CurrentVote) {
			?><option value="<?php echo $CurrentVote['ID']; ?>"<?php if (isset($VoteID) && $CurrentVote['ID'] === $VoteID) { ?>selected="selected"<?php } ?>><?php echo bbify(htmlentities($CurrentVote['Question'])); ?></option><?php
		} ?>
	</select><br />
	Option: <input type="text" name="option" required value="<?php if (isset($PreviewOption)) { echo htmlspecialchars($PreviewOption); } ?>" /><br />
	<?php echo $VoteFormPage->actionAddOption->html(); ?>&nbsp;<?php echo $VoteFormPage->actionPreviewOption->html(); ?>
</form>

<?php
}

}
