<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\Player;

class MessageCouncilRenderer {

public static function render(string $RaceName, string $SendHREF, Player $ThisPlayer): void {
?>
<form method="POST" action="<?php echo $SendHREF; ?>">
	<p>
		<small>
			<b>From: </b><?php echo $ThisPlayer->getDisplayName(); ?><br />
			<b>To:</b> Ruling Council of <?php echo $RaceName; ?>
		</small>
	</p>

	<textarea spellcheck="true" name="message" required></textarea>
	<br /><br />
	<?php echo create_submit_display('Send message'); ?>
</form>

<?php
}

}
