<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Account;
use Smr\Alliance;
use Smr\Epoch;

class AllianceMotdRenderer {

	/**
	 * @param array<string, array<string, string>> $ResponseInputs
	 */
	public static function render(
		Alliance $Alliance,
		?int $OpTime,
		?string $OpResponseHREF,
		array $ResponseInputs,
		?string $EditHREF,
		?string $DiscordServer,
		Account $ThisAccount,
	): void {
		?>
		<div class="center">

		<?php
		if ($OpTime !== null) { ?>
			<table class="center nobord opResponse">
				<tr><th>ENCRYPTED ALLIANCE TELEGRAM</th></tr>
				<tr><td>Your leader has scheduled an important alliance operation for <?php echo date($ThisAccount->getDateTimeFormat(), $OpTime); ?></td></tr>
				<tr><td><span id="countdown"><?php echo format_time($OpTime - Epoch::time()); ?></span></td></tr>
				<tr><td><b>Will you join the operation?</b></td></tr>
				<tr><td>
					<form method="POST" action="<?php echo $OpResponseHREF; ?>"><?php
						foreach ($ResponseInputs as $option => $fields) { ?>
							<span style="padding: 0 4px 0 4px">
								<?php echo create_submit('op_response', $option, fields: $fields); ?>
							</span><?php
						} ?>
					</form>
				</td></tr>
			</table><br /><?php
		}

		if ($Alliance->hasImageURL()) { ?>
			<img class="alliance" src="<?php echo htmlspecialchars($Alliance->getImageURL()); ?>" alt="">
			<br /><br /><?php
		} ?>

		<span class="yellow">Message from your leader</span>
		<p><?php echo bbify($Alliance->getMotD()); ?></p>

		<?php
		if ($EditHREF !== null) { ?>
			<div class="buttonA">
				<a class="buttonA" href="<?php echo $EditHREF; ?>">Edit</a>
			</div><?php
		}

		if ($DiscordServer !== null) { ?>
			<br /><br />
			<iframe src="https://discordapp.com/widget?id=<?php echo $DiscordServer; ?>&amp;theme=dark" width="350" height="375" allowtransparency="true" frameborder="0"></iframe>
			<?php
		} ?>

		</div>

		<?php
	}

}
