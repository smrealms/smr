<p><a href="<?php echo $BackHref; ?>">Back</a></p><?php
if(isset($Comments)) { ?>
	<table class="standard fullwidth">
		<tr>
			<th>Poster</th>
			<th>Comment</th>
			<th>Time</th>
		</tr><?php
		foreach($Comments as &$Comment) { ?>
			<tr class="center">
				<td class="shrink noWrap top"><?php
				if($Comment['Anonymous']) {
					?>Anonymous<?php
				}
				else {
					echo $Comment['PosterAccount']->getHofName();
				}
				if($FeatureModerator) {
					?> - <?php echo $Comment['PosterAccount']->getLogin(); ?>&nbsp;(<?php echo $Comment['PosterAccount']->getAccountID(); ?>)</td><?php
				} ?>
				<td class="left"><?php echo bbifyMessage($Comment['Message']); ?></td>
				<td class="shrink noWrap top"><?php echo $Comment['Time']; ?></td>
			</tr><?php
		} unset($Comment); ?>
	</table><?php
} ?>
<p>
	<form name="FeatureRequestCommentForm" method="POST" action="<?php echo $FeatureRequestCommentFormHREF; ?>">
		<table>
			<tr>
				<td align="center">Comment:</td>
			</tr>
			<tr>
				<td align="center"><textarea spellcheck="true" name="comment" id="InputFields"></textarea></td>
			</tr>
			<tr>
				<td align="center">Anonymous: <input name="anon" id="InputFields" type="checkbox" checked="checked"/></td>
			</tr>
			<tr>
				<td align="center"><input type="submit" name="action" value="Add Comment" id="InputFields"></td>
			</tr>
		</table>
	</form>
</p>