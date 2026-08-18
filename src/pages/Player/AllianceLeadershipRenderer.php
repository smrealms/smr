<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Player;

class AllianceLeadershipRenderer {

	/** @param array<int, Player> $AlliancePlayers */
	public static function render(Player $ThisPlayer, string $HandoverHREF, array $AlliancePlayers): void {
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
			<?php echo create_submit_display('Handover Leadership'); ?>
		</form>
		<?php
	}

}
