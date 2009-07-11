<?php
if(isset($FeatureRequests))
{ ?>
	<form name="FeatureRequestVoteForm" method="POST" action="<?php echo $FeatureRequestVoteFormHREF; ?>">
		<table class="standard" width="100%">
			<tr><?php
				if($FeatureModerator)
				{
					?><th width="30">Requester</th><?php
				} ?>
				<th width="30">Votes (Fav/Yes/No)</th>
				<th>Feature</th>
				<th width="20">Favourite</th>
				<th width="20">Yes</th>
				<th width="20">No</th><?php
				if($FeatureModerator)
				{
					?><th width="20">&nbsp;</th><?php
				} ?>
			</tr><?php
			foreach($FeatureRequests as &$FeatureRequest)
			{ ?>
				<tr style="text-align:center;"><?php
					if($FeatureModerator)
					{
						?><td><?php echo $FeatureRequest['RequestAccount']->getLogin(); ?>&nbsp;(<?php echo $FeatureRequest['RequestAccount']->getAccountID(); ?>)</td><?php
					} ?>
					<td><?php echo $FeatureRequest['Votes']['FAVOURITE']; ?> / <?php echo $FeatureRequest['Votes']['YES']; ?> / <?php echo $FeatureRequest['Votes']['NO']; ?></td>
					<td style="text-align:left;"><?php echo $FeatureRequest['Message']; ?></td>
					<td><input type="radio" name="favourite" value="<?php echo $FeatureRequest['RequestID']; ?>"<?php if($FeatureRequest['VotedFor'] == 'FAVOURITE') { ?> checked="checked"<?php } ?>></td>
					<td><input type="radio" name="vote[<?php echo $FeatureRequest['RequestID']; ?>]" value="YES"<?php if($FeatureRequest['VotedFor'] == 'YES') { ?> checked="checked"<?php } ?>></td>
					<td><input type="radio" name="vote[<?php echo $FeatureRequest['RequestID']; ?>]" value="NO"<?php if($FeatureRequest['VotedFor'] == 'NO') { ?> checked="checked"<?php } ?>></td><?php
					if($FeatureModerator)
					{
						?><td valign="middle" align="center"><input type="checkbox" name="delete[]" value="<?php echo $FeatureRequest['RequestID']; ?>"></td><?php
					} ?>
				</tr><?php
			} unset($FeatureRequest); ?>
		</table>
		<div align="right"><input type="submit" name="action" value="Vote"><?php
			if($FeatureModerator)
			{
				?>&nbsp;<input type="submit" name="action" value="Delete"><?php
			} ?>
		</div>
	</form><?php
} ?>
<p>
	<form name="FeatureRequestForm" method="POST" action="<?php echo $FeatureRequestFormHREF; ?>">
		<table border="0" cellpadding="5">
			<tr>
				<td align="center">Please describe the feature here:</td>
			</tr>
			<tr>
				<td align="center"><textarea name="feature" id="InputFields" style="width:350px;height:100px;"></textarea></td>
			</tr>
			<tr>
				<td align="center"><input type="submit" name="action" value="Submit New Feature" id="InputFields"></td>
			</tr>
		</table>
	</form>
</p>