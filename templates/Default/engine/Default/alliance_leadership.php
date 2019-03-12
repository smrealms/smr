Please select the new Leader:

<form method="POST" action="<?php echo $HandoverHREF; ?>">
	<select name="leader_id" class="InputFields" size="1">
		<?php
		foreach ($AlliancePlayers as $alliancePlayer) {
			$selected = $alliancePlayer->equals($ThisPlayer) ? 'selected="selected"' : '';
			?>
			<option value="<?php echo $alliancePlayer->getAccountID(); ?>" <?php echo $selected; ?>>
				<?php echo $alliancePlayer->getDisplayName(); ?>
			</option><?php
		} ?>
	</select>
	<br /><br />
	<input type="submit" name="action" class="InputFields" value="Handover Leadership" />
</form>
