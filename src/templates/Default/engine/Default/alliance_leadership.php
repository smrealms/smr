<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 * @var string $HandoverHREF
 * @var array<int, Smr\Player> $AlliancePlayers
 */

?>
Please select the new Leader:

<form method="POST" action="<?php echo $HandoverHREF; ?>">
	<select name="leader_id" size="1">
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
	<?php echo create_submit('action', 'Handover Leadership'); ?>
</form>
