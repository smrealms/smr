<p style="width:60%; text-align:justify;">Do you enjoy Space Merchant Realms?
Would you like to see the game grow? If your answer is yes, then consider
making a donation! Your donation will translate into SMR credits that you can
use in-game for name changes, message notifications, painting your ship, and
more!</p>
Current donation rate is: $<?php echo number_format($TotalDonation / 3, 2); ?> per month (within last 3 months).

<br /><br />

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="mrspock@smrealms.de">
	<input type="hidden" name="item_name" value="Support development with money.">
	<input type="hidden" name="item_number" value="<?php echo $ThisAccount->getAccountID(); ?>">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="lc" value="US">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

<br /><br />

<h2>Message Notifications</h2>
<p>Want to receive emails when you get a message? Well now you can!</p>
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBuyMessageNotificationsHREF(); ?>">Buy Message Notifications</a>
</div>

<?php
if(isset($GameID)) { ?>
	<br />
	<br />
	<br />
	<h2>Ship Name</h2>
	<p>Customise your ship name, or even spray on your favourite logo!</p>
	<div class="buttonA">
		<a class="buttonA" href="<?php echo Globals::getBuyShipNameHref(); ?>">Customize Ship Name (<?php echo min(CREDITS_PER_TEXT_SHIP_NAME, CREDITS_PER_HTML_SHIP_NAME, CREDITS_PER_SHIP_LOGO); ?>-<?php echo max(CREDITS_PER_TEXT_SHIP_NAME, CREDITS_PER_HTML_SHIP_NAME, CREDITS_PER_SHIP_LOGO); ?> SMR Credits)</a>
	</div><?php
}
