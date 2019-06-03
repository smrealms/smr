<table>
	<tr>
		<th>Action</th>
		<th>Category</th>
		<th>Description</th>
		<th>Count</th>
	</tr><?php
	foreach ($CategoryTable as $Category => $Info) { ?>
		<tr<?php if ($Info['Selected']) { echo ' class="bold"'; } ?>>
			<td class="center"><a href="<?php echo $Info['HREF']; ?>">View</a></td>
			<td><?php echo $Category; ?></td>
			<td><?php echo $Info['Description']; ?></td>
			<td class="center"><?php echo $Info['Count']; ?></td>
		</tr><?php
	} ?>
</table>

<?php
if (isset($FeatureRequests)) { ?>
	<form name="FeatureRequestVoteForm" method="POST" action="<?php echo $FeatureRequestVoteFormHREF; ?>">
		<div class="right"><?php
			if ($CanVote) { ?>
				<input type="submit" name="action" value="Vote"><?php
			} ?>
		</div><br />
		<table class="standard fullwidth">
			<tr><?php
				if ($FeatureModerator) {
					?><th width="30">Requester</th><?php
				} ?>
				<th width="30">Votes (Fav/Yes/No)</th>
				<th>Feature</th>
				<th>Comments</th><?php
				if ($CanVote) { ?>
					<th width="20">Favourite</th>
					<th width="20">Yes</th>
					<th width="20">No</th><?php
				}
				if ($FeatureModerator) {
					?><th width="20">&nbsp;</th><?php
				} ?>
			</tr><?php
			foreach ($FeatureRequests as $FeatureRequest) { ?>
				<tr class="center"><?php
					if ($FeatureModerator) {
						?><td><?php echo $FeatureRequest['RequestAccount']->getLogin(); ?>&nbsp;(<?php echo $FeatureRequest['RequestAccount']->getAccountID(); ?>)</td><?php
					} ?>
					<td><span class="bold green"><?php echo $FeatureRequest['Votes']['FAVOURITE']; ?></span> / <span class="green"><?php echo $FeatureRequest['Votes']['YES'] + $FeatureRequest['Votes']['FAVOURITE']; ?></span> / <span class="red"><?php echo $FeatureRequest['Votes']['NO']; ?></span></td>
					<td class="left"><?php echo bbifyMessage($FeatureRequest['Message']); ?></td>
					<td class="shrink noWrap top"><a href="<?php echo $FeatureRequest['CommentsHREF']; ?>">View (<?php echo $FeatureRequest['Comments']; ?>)</a></td><?php
					if ($CanVote) { ?>
						<td><input type="radio" name="favourite" value="<?php echo $FeatureRequest['RequestID']; ?>"<?php if ($FeatureRequest['VotedFor'] == 'FAVOURITE') { ?> checked="checked"<?php } ?>></td>
						<td><input type="radio" name="vote[<?php echo $FeatureRequest['RequestID']; ?>]" value="YES"<?php if ($FeatureRequest['VotedFor'] == 'YES' || $FeatureRequest['VotedFor'] == 'FAVOURITE') { ?> checked="checked"<?php } ?>></td>
						<td><input type="radio" name="vote[<?php echo $FeatureRequest['RequestID']; ?>]" value="NO"<?php if ($FeatureRequest['VotedFor'] == 'NO') { ?> checked="checked"<?php } ?>></td><?php
					}
					if ($FeatureModerator) {
						?><td valign="middle" class="center"><input type="checkbox" name="set_status_ids[]" value="<?php echo $FeatureRequest['RequestID']; ?>"></td><?php
					} ?>
				</tr><?php
			} ?>
		</table>
		<div class="right"><?php
			if ($FeatureModerator) { ?>&nbsp;
				<select name="status">
					<option disabled selected value style="display:none"> -- Select Status -- </option>
					<option value="Accepted">Accepted</option>
					<option value="Implemented">Implemented</option>
					<option value="Rejected">Rejected</option>
					<option value="Opened">Open</option>
					<option value="Deleted">Delete</option>
				</select>&nbsp;<input type="submit" name="action" value="Set Status"><?php
			}
			if ($CanVote) { ?>
				<input type="submit" name="action" value="Vote"><?php
			} ?>
		</div><br />
	</form><?php
} ?>

<br />
<form name="FeatureRequestForm" method="POST" action="<?php echo $FeatureRequestFormHREF; ?>">
	<table>
		<tr>
			<td class="center">Please describe your requested feature here:</td>
		</tr>
		<tr>
			<td class="center"><textarea spellcheck="true" name="feature" class="InputFields" maxlength="500"></textarea></td>
		</tr>
		<tr>
			<td class="center">Anonymous: <input name="anon" class="InputFields" type="checkbox" checked="checked"/></td>
		</tr>
		<tr>
			<td class="center"><input type="submit" name="action" value="Submit New Feature" class="InputFields"></td>
		</tr>
	</table>
</form>
