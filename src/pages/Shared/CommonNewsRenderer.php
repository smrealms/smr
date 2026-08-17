<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Account;

class CommonNewsRenderer {

	/**
	 * @param ?array{Time: int, Message: string} $BreakingNews
	 * @param ?array{Time: int, Message: string} $LottoNews
	 */
	public static function render(
		Account $ThisAccount,
		?array $BreakingNews,
		?array $LottoNews,
	): void {
		if ($BreakingNews !== null) {
			?><b>MAJOR NEWS! - <?php echo date($ThisAccount->getDateTimeFormat(), $BreakingNews['Time']); ?></b><br />
			<table class="standard">
				<tr>
					<th><span class="lgreen">Time</span></th>
					<th><span class="lgreen">Breaking News</span></th>
				</tr>
				<tr>
					<td class="center"><?php echo date($ThisAccount->getDateTimeFormat(), $BreakingNews['Time']); ?></td>
					<td class="left"><?php echo $BreakingNews['Message']; ?></td>
				</tr>
			</table>
			<br /><br /><?php
		}

		if ($LottoNews !== null) { ?>
			<b>Lotto News</b><br />
			<table class="standard">
				<tr>
				    <th><span class="lgreen">Time</span></th>
				    <th><span class="lgreen">Message</span></th>
			    </tr>
			    <tr>
				    <td class="center"><?php echo date($ThisAccount->getDateTimeFormat(), $LottoNews['Time']); ?></td>
				    <td class="left"><?php echo $LottoNews['Message']; ?></td>
			    </tr>
		    </table>
			<br /><br /><?php
		}

	}

}
