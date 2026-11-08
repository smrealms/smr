<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class VoteCreateRenderer {

	/**
	 * @param array<int, array{ID: int, Question: string}> $CurrentVotes
	 * @param array<string> $CurrentOptions
	 */
	public static function render(
		VoteCreateProcessor $VoteFormPage,
		string $SelectVoteHREF,
		array $CurrentVotes,
		array $CurrentOptions,
		?string $PreviewVote,
		?int $Days,
		?string $PreviewOption,
		?int $VoteID,
	): void {
		if ($PreviewVote !== null) { ?><table class="standard"><tr><td><?php echo bbify(htmlentities($PreviewVote)); ?></td></tr></table><?php } ?>
		<form name="VoteForm" method="POST" action="<?php echo $VoteFormPage->href(); ?>">
			Question: <input type="text" name="question" required value="<?php if ($PreviewVote !== null) { echo bbify(htmlentities($PreviewVote)); } ?>" /><br />
			Days to end: <input type="number" name="days" required value="<?php if ($Days !== null) { echo $Days; } ?>" /><br />
			<?php echo $VoteFormPage->actionCreateVote->html(); ?>&nbsp;<?php echo $VoteFormPage->actionPreviewVote->html(); ?>
		</form>
		<br /><br />

			<?php if ($PreviewOption !== null) { ?><table class="standard"><tr><td><?php echo bbify($PreviewOption); ?></td></tr></table><?php } ?>
		<form name="VoteSelectForm" method="POST" action="<?php echo $SelectVoteHREF; ?>">
			Vote: <select id="vote" name="vote" onchange="this.form.submit()"><?php
				foreach ($CurrentVotes as $CurrentVote) {
					?><option value="<?php echo $CurrentVote['ID']; ?>" <?php if ($VoteID !== null && $CurrentVote['ID'] === $VoteID) { ?>selected="selected"<?php } ?>><?php echo bbify(htmlentities($CurrentVote['Question'])); ?></option><?php
				} ?>
			</select><br />
		</form>
		<form name="VoteForm" method="POST" action="<?php echo $VoteFormPage->href(); ?>">
			<input type="hidden" name="vote" value="<?php echo $VoteID; ?>" />
			Option: <input type="text" name="option" required value="<?php if ($PreviewOption !== null) { echo htmlspecialchars($PreviewOption); } ?>" /><br />
			<?php echo $VoteFormPage->actionAddOption->html(); ?>&nbsp;<?php echo $VoteFormPage->actionPreviewOption->html(); ?>
		</form>
		<?php if ($VoteID !== null) { ?>
			<br />
			Current Options:<?php
			if (count($CurrentOptions) === 0) { ?>
				None<?php
			} else { ?>
				<ul><?php
				foreach ($CurrentOptions as $CurrentOption) { ?>
					<li><?php echo bbify($CurrentOption); ?></li><?php
				} ?>
				</ul><?php
			}
		} ?>

		<?php
	}

}
