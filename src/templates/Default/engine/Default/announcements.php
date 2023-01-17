<?php declare(strict_types=1);

/**
 * @var Smr\Account $ThisAccount
 * @var array<array{Time: int, Msg: string}> $Announcements
 * @var string $ContinueHREF
 */

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
				<?php echo bbifyMessage($Announcement['Msg']); ?>
			</td>
		</tr><?php
	} ?>

</table>
<br />

<div class="buttonA">
	<a class="buttonA" href="<?php echo $ContinueHREF; ?>">Continue</a>
</div>
