<?php
// Create an array for use later
$TradeCalcInfo = [
	$Amount,
	$Good['BasePrice'],
	$Port->getGoodDistance($Good['ID']),
	$Port->getGoodAmount($Good['ID']),
	$Good['Max'],
	$ThisPlayer->getRelation($Port->getRaceID()),
];

if (isset($OfferToo)) { ?>
	<p class="red">I can't accept your offer. It's too <?php echo $OfferToo; ?>.</p><?php
} ?>

<p>I would <?php echo $PortAction; ?> <?php echo $Amount; ?> units of <?php echo $Good['Name']; ?> for <span class="creds"><?php echo $OfferedPrice; ?></span> credits!<br />
Note: In order to maximize your experience you have to bargain with the port owner, unless you have maximum relations (1000) with that race, which gives full experience without the need to bargain.</p>

<form name="FORM" method="POST" action="<?php echo $BargainHREF; ?>">
	<input type="number" name="bargain_price" value="<?php echo $BargainPrice; ?>" class="center" style="width:75;vertical-align:middle;" autofocus>&nbsp;
	<!-- all needed information to calculate the ideal price -->
	<!-- Trade.Amount:Good.BasePrice:Good.Distance:Port.Good.Amount:Port.Good.Max:Relations -->
	<!-- (<?php echo join(':', $TradeCalcInfo); ?>)-->
	<input type="submit" name="action" value="Bargain (1)" />
</form>

<p>Distance Index: <?php echo $Port->getGoodDistance($Good['ID']); ?></p>

<h2>Or do you want to:</h2>
<p><a href="<?php echo $ShopHREF; ?>" class="submitStyle">Select a different good</a></p>
<p><a href="<?php echo $LeaveHREF; ?>" class="submitStyle">Leave Port</a></p>
