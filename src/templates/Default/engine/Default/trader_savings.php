<?php declare(strict_types=1);

/**
 * @var array<array{ID: int, Password: string}> $AnonAccounts
 * @var int $LottoTickets
 * @var array{Prize: int, TimeRemaining: int} $LottoInfo
 * @var float $LottoWinChance
 * @var int $WinningTickets
 */

?>
<div>
	<h2>Anonymous Accounts</h2>
	<br />
	<?php
	if (count($AnonAccounts) > 0) { ?>
		You own the following accounts:<br /><br /><?php
		foreach ($AnonAccounts as $Acc) { ?>
			Account <span class="yellow"><?php echo $Acc['ID']; ?></span> with password <span class="yellow"><?php echo htmlentities($Acc['Password']); ?></span>
			<br /><?php
		}
	} else { ?>
		You own no anonymous accounts.<?php
	} ?>
</div>
<br /><br />
<div>
	<h2>Lotto Tickets</h2>
	<br />You own <span class="yellow"><?php echo $LottoTickets; ?></span> Lotto Tickets.
	<br />There are <?php echo format_time($LottoInfo['TimeRemaining']); ?> remaining until the next drawing.
	<br />Currently you have a <?php echo $LottoWinChance; ?>% chance to win.
	<?php
	if ($WinningTickets > 0) { ?>
		<br /><br />You own <?php echo $WinningTickets; ?> winning tickets. You should go to the bar to claim your prize.<?php
	} ?>
</div>
