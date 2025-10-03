<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Port $Port
 * @var Smr\TradeGood $Good
 * @var int $Amount
 * @var int $OfferedPrice
 * @var int $BargainPrice
 * @var string $ShopHREF
 * @var string $LeaveHREF
 * @var string $BargainHREF
 * @var string $PortAction
 */

// Create an array for use later
$TradeCalcInfo = [
	$Amount,
	$Good->basePrice,
	$Port->getGoodDistance($Good->id),
	$Port->getGoodAmount($Good->id),
	$Good->maxPortAmount,
	$ThisPlayer->getRelation($Port->getRaceID()),
];

if (isset($OfferToo)) { ?>
	<p class="red">I can't accept your offer. It's too <?php echo $OfferToo; ?>.</p><?php
} ?>

<p>I would <?php echo $PortAction; ?> <?php echo $Amount; ?> units of <?php echo $Good->name; ?> for <span class="creds"><?php echo $OfferedPrice; ?></span> credits!<br />
Note: In order to maximize your experience you have to bargain with the port owner, unless you have maximum relations (1000) with that race, which gives full experience without the need to bargain.</p>

<form name="FORM" method="POST" action="<?php echo $BargainHREF; ?>">
	<input type="number" name="bargain_price" value="<?php echo $BargainPrice; ?>" min="1" required class="center" style="width:75;vertical-align:middle;" autofocus>&nbsp;
	<!-- all needed information to calculate the ideal price -->
	<!-- Trade.Amount:Good.BasePrice:Good.Distance:Port.Good.Amount:Port.Good.Max:Relations -->
	<!-- (<?php echo implode(':', $TradeCalcInfo); ?>)-->
	<?php echo create_submit('action', 'Bargain (1)'); ?>
</form>

<p>Distance Index: <?php echo $Port->getGoodDistance($Good->id); ?></p>

<h2>Or do you want to:</h2>
<p><a href="<?php echo $ShopHREF; ?>" class="submitStyle">Select a different good</a></p>
<p><a href="<?php echo $LeaveHREF; ?>" class="submitStyle">Leave Port</a></p>
