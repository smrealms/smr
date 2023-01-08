<?php declare(strict_types=1);

/**
 * @var string $PlayerStatus
 * @var string $DealerStatus
 * @var string $PlayerHand
 * @var string $DealerHand
 * @var string $ResultMsg
 * @var ?string $HitHREF
 * @var ?string $StayHREF
 * @var ?string $BetHREF
 * @var ?int $Bet
 */

?>
<div class="center">
	<?php echo $ResultMsg; ?>
	<div>Dealer's cards are</div><br />
	<?php echo $DealerHand; ?>
	<div><?php echo $DealerStatus; ?></div><br />
	<hr style="border:1px solid green;width:50%" noshade>
	<div>Your cards are</div><br />
	<?php echo $PlayerHand; ?>
	<div><?php echo $PlayerStatus; ?></div><br />
	<?php echo $Winnings ?? ''; ?>
	<?php if (isset($BetHREF)) { ?>
		<p><a class="submitStyle" href="<?php echo $BetHREF; ?>">Play Some More ($<?php echo $Bet; ?>)</a></p><?php
	} else { ?>
		<a class="submitStyle" href="<?php echo $HitHREF; ?>">HIT</a>
		<br /><br />
		<a class="submitStyle" href="<?php echo $StayHREF; ?>">STAY</a><?php
	} ?>
</div>
