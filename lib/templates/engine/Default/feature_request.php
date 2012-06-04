<?php
if(isset($FeatureRequests))
{ ?>
	<form name="FeatureRequestVoteForm" method="POST" action="<?php echo $FeatureRequestVoteFormHREF; ?>">
		<p>
			<table class="standard" width="100%">
				<tr><?php
					if($FeatureModerator)
					{
						?><th width="30">Requester</th><?php
					} ?>
					<th width="30">Votes</th>
					<th>Feature</th>
					<th width="20">&nbsp;</th><?php
					if($FeatureModerator)
					{
						?><th width="20">&nbsp;</th><?php
					} ?>
				</tr><?php
				foreach($FeatureRequests as &$FeatureRequest)
				{ ?>
					<tr><?php
						if($FeatureModerator)
						{
							?><td valign="top" align="center"><?php echo $FeatureRequest['RequestAccount']->getLogin(); ?>&nbsp;<?php echo $FeatureRequest['RequestAccount']->getAccountID(); ?>)</td><?php
						} ?>
						<td valign="top" align="center"><?php echo $FeatureRequest['Votes']; ?></td>
						<td valign="top"><?php echo $FeatureRequest['Message']; ?></td>
						<td valign="middle" align="center"><input type="radio" name="vote" value="<?php echo $FeatureRequest['RequestID']; ?>"<?php if($FeatureRequest['VotedFor']) { ?> checked="checked"<?php } ?>></td><?php
						if($FeatureModerator)
						{
							?><td valign="middle" align="center"><input type="checkbox" name="delete[]" value="<?php echo $FeatureRequest['RequestID']; ?>"></td><?php
						} ?>
					</tr><?php
				} unset($FeatureRequest); ?>
			</table>
		</p>
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