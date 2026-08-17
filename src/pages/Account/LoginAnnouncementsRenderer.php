<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;

class LoginAnnouncementsRenderer {

	/**
	 * @param array<array{Time: int, Msg: string}> $Announcements
	 */
	public static function render(array $Announcements, string $ContinueHREF, Account $ThisAccount): void {
		?>
		<table class="standard fullwidth">
			<tr>
				<th>Time</th>
				<th>Message</th>
			</tr>

			<?php
			foreach ($Announcements as $Announcement) { ?>
				<tr>
					<td class="shrink top noWrap">
						<?php echo date($ThisAccount->getDateTimeFormatSplit(), $Announcement['Time']); ?>
					</td>
					<td class="top">
						<?php echo bbify($Announcement['Msg']); ?>
					</td>
				</tr><?php
			} ?>

		</table>
		<br />

		<div class="buttonA">
			<a class="buttonA" href="<?php echo $ContinueHREF; ?>">Continue</a>
		</div>

		<?php
	}

}
