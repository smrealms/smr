<?php declare(strict_types=1);

namespace Smr\Pages\Account;

class FeatureRequestCommentsRenderer {

/**
 * @param array<int, array{CommentID: int, Message: string, Time: string, Name: string}> $Comments
 */
public static function render(
	string $BackHref,
	bool $FeatureModerator,
	int $FeatureRequestId,
	?FeatureRequestVoteProcessor $FeatureRequestStatusFormPage,
	array $Comments,
	string $FeatureRequestCommentFormHREF,
): void {
?>
<p><a href="<?php echo $BackHref; ?>">Back</a></p><?php
if (count($Comments) > 0) { ?>
	<table class="standard fullwidth">
		<tr>
			<th>Poster</th>
			<th>Comment</th>
			<th>Time</th>
		</tr><?php
		foreach ($Comments as $Comment) { ?>
			<tr class="center">
				<td class="shrink noWrap top"><?php echo $Comment['Name']; ?></td>
				<td class="left"><?php echo bbify(htmlentities($Comment['Message'])); ?></td>
				<td class="shrink noWrap top"><?php echo $Comment['Time']; ?></td>
			</tr><?php
		} ?>
	</table><?php
}

if ($FeatureModerator && $FeatureRequestStatusFormPage !== null) { ?>
	<form name="FeatureRequestStatusForm" method="POST" action="<?php echo $FeatureRequestStatusFormPage->href(); ?>">
		<div class="right">&nbsp;
			<select name="status">
				<option disabled selected value style="display:none"> -- Select Status -- </option>
				<option value="Accepted">Accepted</option>
				<option value="Implemented">Implemented</option>
				<option value="Rejected">Rejected</option>
				<option value="Opened">Open</option>
				<option value="Deleted">Delete</option>
			</select>&nbsp;
			<input type="hidden" name="set_status_ids[]" value="<?php echo $FeatureRequestId; ?>" />
			<?php echo $FeatureRequestStatusFormPage->actionSetStatus->html(); ?>
		</div>
	</form><?php
} ?>

<br />
<form name="FeatureRequestCommentForm" method="POST" action="<?php echo $FeatureRequestCommentFormHREF; ?>">
	<table>
		<tr>
			<td class="center">Comment:</td>
		</tr>
		<tr>
			<td class="center"><textarea spellcheck="true" name="comment" required></textarea></td>
		</tr>
		<tr>
			<td class="center">Anonymous: <input name="anon" type="checkbox" checked="checked"/></td>
		</tr>
		<tr>
			<td class="center"><?php echo create_submit_display('Add Comment'); ?></td>
		</tr>
	</table>
</form>

<?php
}

}
