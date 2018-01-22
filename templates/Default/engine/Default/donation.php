<p style="width:60%; text-align:justify;">Do you enjoy Space Merchant Realms? Would you like to see the game grow? If your answer is yes, then consider making a donation! Your donation will translate into SMR credits that you can use in game to get nifty items, and will also help the game improve.</p>
Current donation rate is: $<?php echo number_format($TotalDonation / 90*7, 2); ?> per week (within last 3 months).

<br /><br /><br />

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

<br />
<br />
<p>Thank you for your donation.</p><br />


<h2>Message Notifications</h2><br />
Want to receive emails when you get a message? Well now you can!<br />
<br />
<div class="buttonA">
	<a class="buttonA" href="<?php echo Globals::getBuyMessageNotificationsHREF(); ?>">&nbsp;Buy Message Notifications&nbsp;</a>
</div>

<?php
if(isset($GameID)) { ?>
	<br />
	<br />
	<br />
	<h2>Ship</h2><br />
	Well... of course you could always pay our painters to customise your ship name, or even spray on your favourite logo!<br />
	<br />
	<div class="buttonA">
		<a class="buttonA" href="<?php echo Globals::getBuyShipNameHref(); ?>">&nbsp;Customize Ship Name (<?php echo min(CREDITS_PER_TEXT_SHIP_NAME, CREDITS_PER_HTML_SHIP_NAME, CREDITS_PER_SHIP_LOGO); ?>-<?php echo max(CREDITS_PER_TEXT_SHIP_NAME, CREDITS_PER_HTML_SHIP_NAME, CREDITS_PER_SHIP_LOGO); ?> SMR Credits)&nbsp;</a>
	</div><?php
}
/*
if(isset($GameID)) { ?>
	<h2>Maps</h2><br />
	New intelligence has just come in! We now have full maps of EVERY galaxy!  We are willing to sell you the newest maps of each galaxy for <?php echo CREDITS_PER_GAL_MAP; ?> SMR credits each!<br />
	<br />
	<div class="buttonA">
		<a class="buttonA" href="<?php echo Globals::getBuyGalMapHREF(); ?>">&nbsp;Buy a Galaxy Map (<?php echo CREDITS_PER_GAL_MAP; ?> SMR Credits)&nbsp;</a>
	</div><br /><br /><?php
} */ ?>
