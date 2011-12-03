<?php
if(!$ShowCurrent)
{
	?><p><a href="<?php echo Globals::getFeatureRequestHREF(); ?>">View Current Feature Requests</a></p><?php
}
if($Status != 'Opened' || $ShowCurrent)
{
	?><p><a href="<?php echo $ShowOldFeaturesHref; ?>">View Old Requests</a></p><?php
}
if($Status != 'Implemented')
{
	?><p><a href="<?php echo $ViewImplementedFeaturesHref; ?>">View Previously Implemented Features</a></p><?php
}
if($Status != 'Rejected')
{
	?><p><a href="<?php echo $ShowRejectedFeaturesHref; ?>">View Rejected Requests</a></p><?php
}
if(isset($FeatureRequests))
{ ?>
	<form name="FeatureRequestVoteForm" method="POST" action="<?php echo $FeatureRequestVoteFormHREF; ?>">
		<div align="right"><?php
			if($Status == 'Opened')
			{ ?>
				<input type="submit" name="action" value="Vote"><?php
			} ?>
		</div><br />
		<table class="standard fullwidth">
			<tr><?php
				if($FeatureModerator)
				{
					?><th width="30">Requester</th><?php
				} ?>
				<th width="30">Votes (Fav/Yes/No)</th>
				<th>Feature</th>
				<th>Comments</th><?php
				if($Status == 'Opened')
				{ ?>
					<th width="20">Favourite</th>
					<th width="20">Yes</th>
					<th width="20">No</th><?php
				}
				if($FeatureModerator)
				{
					?><th width="20">&nbsp;</th><?php
				} ?>
			</tr><?php
			foreach($FeatureRequests as &$FeatureRequest)
			{ ?>
				<tr class="center"><?php
					if($FeatureModerator)
					{
						?><td><?php echo $FeatureRequest['RequestAccount']->getLogin(); ?>&nbsp;(<?php echo $FeatureRequest['RequestAccount']->getAccountID(); ?>)</td><?php
					} ?>
					<td><?php echo $FeatureRequest['Votes']['FAVOURITE']; ?> / <?php echo $FeatureRequest['Votes']['YES']; ?> / <?php echo $FeatureRequest['Votes']['NO']; ?></td>
					<td style="text-align:left;"><?php echo bbifyMessage($FeatureRequest['Message']); ?></td>
					<td class="shrink noWrap top"><a href="<?php echo $FeatureRequest['CommentsHREF']; ?>">View (<?php echo $FeatureRequest['Comments']; ?>)</a></td><?php
					if($Status == 'Opened')
					{ ?>
						<td><input type="radio" name="favourite" value="<?php echo $FeatureRequest['RequestID']; ?>"<?php if($FeatureRequest['VotedFor'] == 'FAVOURITE') { ?> checked="checked"<?php } ?>></td>
						<td><input type="radio" name="vote[<?php echo $FeatureRequest['RequestID']; ?>]" value="YES"<?php if($FeatureRequest['VotedFor'] == 'YES') { ?> checked="checked"<?php } ?>></td>
						<td><input type="radio" name="vote[<?php echo $FeatureRequest['RequestID']; ?>]" value="NO"<?php if($FeatureRequest['VotedFor'] == 'NO') { ?> checked="checked"<?php } ?>></td><?php
					}
					if($FeatureModerator)
					{
						?><td valign="middle" align="center"><input type="checkbox" name="delete[]" value="<?php echo $FeatureRequest['RequestID']; ?>"></td><?php
					} ?>
				</tr><?php
			} unset($FeatureRequest); ?>
		</table>
		<div align="right"><?php
			if($FeatureModerator)
			{
				?>&nbsp;<select name="status"><option value="Implemented">Implemented</option><option value="Rejected">Rejected</option><option value="Opened">Open</option><option value="Deleted">Delete</option></select>&nbsp;<input type="submit" name="action" value="Set Status"><?php
			}
			if($Status == 'Opened')
			{ ?>
				<input type="submit" name="action" value="Vote"><?php
			} ?>
		</div><br />
	</form><?php
} ?>
<p>
	<form name="FeatureRequestForm" method="POST" action="<?php echo $FeatureRequestFormHREF; ?>">
		<table>
			<tr>
				<td align="center">Please describe the feature here:</td>
			</tr>
			<tr>
				<td align="center"><textarea name="feature" id="InputFields"></textarea></td>
			</tr>
			<tr>
				<td align="center">Anonymous: <input name="anon" id="InputFields" type="checkbox" checked="checked"/></td>
			</tr>
			<tr>
				<td align="center"><input type="submit" name="action" value="Submit New Feature" id="InputFields"></td>
			</tr>
		</table>
	</form>
</p>