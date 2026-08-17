<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Player;
use Smr\Port;
use Smr\TradeGood;

class ShopGoodsNegotiateRenderer {

public static function render(
	?string $OfferToo,
	string $PortAction,
	string $BargainHREF,
	int $BargainPrice,
	int $OfferedPrice,
	TradeGood $Good,
	int $Amount,
	Port $Port,
	string $ShopHREF,
	string $LeaveHREF,
	Player $ThisPlayer,
): void {
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

<p>I would <?php echo $PortAction; ?> <?php echo number_format($Amount); ?> units of <?php echo $Good->name; ?> for <span class="creds"><?php echo number_format($OfferedPrice); ?></span> credits!<br />
Note: In order to maximize your experience you have to bargain with the port owner, unless you have maximum relations (1000) with that race, which gives full experience without the need to bargain.</p>

<form name="FORM" method="POST" action="<?php echo $BargainHREF; ?>">
	<input type="number" name="bargain_price" value="<?php echo $BargainPrice; ?>" min="1" required class="center" style="width:75;vertical-align:middle;" autofocus>&nbsp;
	<!-- all needed information to calculate the ideal price -->
	<!-- Trade.Amount:Good.BasePrice:Good.Distance:Port.Good.Amount:Port.Good.Max:Relations -->
	<!-- (<?php echo implode(':', $TradeCalcInfo); ?>)-->
	<?php echo create_submit_display('Bargain (1)'); ?>
</form>

<p>Distance Index: <?php echo $Port->getGoodDistance($Good->id); ?></p>

<h2>Or do you want to:</h2>
<p><a href="<?php echo $ShopHREF; ?>" class="submitStyle">Select a different good</a></p>
<p><a href="<?php echo $LeaveHREF; ?>" class="submitStyle">Leave Port</a></p>

<?php
}

}
