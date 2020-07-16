<p><a href="<?php echo $BackHref; ?>">Back</a></p><?php
if (isset($Comments)) { ?>
	<table class="standard fullwidth">
		<tr>
			<th>Poster</th>
			<th>Comment</th>
			<th>Time</th>
		</tr><?php
		foreach ($Comments as $Comment) { ?>
			<tr class="center">
				<td class="shrink noWrap top"><?php
				if ($Comment['Anonymous']) {
					?>Anonymous<?php
				} else {
					echo $Comment['PosterAccount']->getHofDisplayName();
				}
				if ($FeatureModerator) {
					?> - <?php echo $Comment['PosterAccount']->getLogin(); ?>&nbsp;(<?php echo $Comment['PosterAccount']->getAccountID(); ?>)</td><?php
				} ?>
				<td class="left"><?php echo bbifyMessage($Comment['Message']); ?></td>
				<td class="shrink noWrap top"><?php echo $Comment['Time']; ?></td>
			</tr><?php
		} ?>
	</table><?php
}

if ($FeatureModerator) { ?>
	<form name="FeatureRequestStatusForm" method="POST" action="<?php echo $FeatureRequestStatusFormHREF; ?>">
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
			<input type="submit" name="action" value="Set Status" />
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
			<td class="center"><input type="submit" name="action" value="Add Comment"></td>
		</tr>
	</table>
</form>
