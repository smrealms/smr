<?

// defines the pixel length of bar
$total_length = 400;

$smarty->assign('PageTopic','DONATIONS');
$PHP_OUTPUT.=('<p style="width:60%; text-align:justify;">Hello,<br /><br />If you enjoy the game I\'d like to ask you to donate a few bucks to
help keep it alive. Every little donation can help me. Beside for the cost for the server
I invest most of my free time into this game to make it better. So if you are able
to give me a financial aid I really would appreciate it.</p>');
$db->query('SELECT SUM(amount) as total_donation FROM account_donated WHERE time > ' . time() . ' - (60 * 60 * 24 * 90)');
if ($db->next_record())
	$total_donation = $db->f('total_donation');

$PHP_OUTPUT.=('Current donation rate is: $' . number_format($total_donation / 12, 2) . ' per week (within last 3 months).');

$PHP_OUTPUT.=('<br /><br /><br />');

// Begin PayPal Logo
$PHP_OUTPUT.=('<form action="https://www.paypal.com/cgi-bin/webscr" method="post">');
$PHP_OUTPUT.=('<input type="hidden" name="cmd" value="_xclick">');
$PHP_OUTPUT.=('<input type="hidden" name="business" value="paypal@chaos-inc.de">');
$PHP_OUTPUT.=('<input type="hidden" name="item_name" value="Support development with money.">');
$PHP_OUTPUT.=('<input type="hidden" name="item_number" value="' . $account->account_id . '">');
$PHP_OUTPUT.=('<input type="hidden" name="no_shipping" value="1">');
$PHP_OUTPUT.=('<input type="hidden" name="no_note" value="1">');
$PHP_OUTPUT.=('<input type="hidden" name="currency_code" value="USD">');
$PHP_OUTPUT.=('<input type="hidden" name="tax" value="0">');
$PHP_OUTPUT.=('<input type="hidden" name="lc" value="US">');
$PHP_OUTPUT.=('<input type="hidden" name="bn" value="PP-DonationsBF">');
$PHP_OUTPUT.=('<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!">');
$PHP_OUTPUT.=('<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">');
$PHP_OUTPUT.=('</form>');
// End PayPal Logo -->

// get donations from db

/*

$components = array();
$components['<b>Processor</b><br><span style="font-size:75%;">AMD ATHLON XP 2700+ Thoroughbred (2166MHz, 166/333 MHz) Socket A Boxed </span>'] = 299.99;
$components['<b>Motherboard</b><br><span style="font-size:75%;">EPOX EP-8K9A3+ Athlon/Duron Socket A UDMA/133 RAID (4x) VIA KT400 DDR Sound USB2.0 LAN AGP 8x</span>'] = 136.99;
$components['<b>Harddisk</b><br><span style="font-size:75%;">2 x SEAGATE Barracuda 7200.7 ST360014A, 60.0GB, 7200Rpm, UDMA/100</span>'] = 169.98;
$components['<b>Graphic Card</b><br><span style="font-size:75%;">VGA 16MB AGP SiS305 AGP 2x Bulk</span>'] = 18.99;
$components['<b>CD-Rom</b><br><span style="font-size:75%;">AOPEN 52x CD-ROM ATAPI Bulk</span>'] = 21.99;
$components['<b>Chassis</b><br><span style="font-size:75%;">19" Chassis 300W</span>'] = 198.99;

$total_price = array_sum($components);

$PHP_OUTPUT.=('<p><table width="100%"><tr>');
$PHP_OUTPUT.=('<tr><th>Component</th><th>Price</th></tr>');

foreach ($components as $component => $price)
	$PHP_OUTPUT.=('<tr><td>$component</td><td align="right">' . number_format($price, 2) . ' �</td></tr>');

$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td align="right"><hr noshade width="100%"></td></tr>');
$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td align="right">' . number_format($total_price, 2) . ' �</td></tr>');

$PHP_OUTPUT.=('</table></p>');

$current_length = $total_length * $donations / $total_price;

$PHP_OUTPUT.=('<div align="center">');
if ($donations < $total_price) {

	$PHP_OUTPUT.=('<span style="font-size:75%;">Current Progress:<br></span>');
	$PHP_OUTPUT.=('<img src="'.$URL.'/images/progress_bar/bar_green_start.jpg">');

	for ($i = 0; $i < $current_length; $i++)
		$PHP_OUTPUT.=('<img src="'.$URL.'/images/progress_bar/bar_green_mid.jpg">');

	for ($i = 0; $i < $total_length - $current_length; $i++)
		$PHP_OUTPUT.=('<img src="'.$URL.'/images/progress_bar/bar_white_mid.jpg">');

	$PHP_OUTPUT.=('<img src="'.$URL.'/images/progress_bar/bar_white_end.jpg">');
	$PHP_OUTPUT.=('<span style="font-size:60%;"><br>' . round(100 * $donations / $total_price) . ' %</span>');

} else
	$PHP_OUTPUT.=('WE DID IT! I'm going to buy the new hardware in a couple of days!');

$PHP_OUTPUT.=('</div>');
*/
$PHP_OUTPUT.=('<p>&nbsp;</p>');
$PHP_OUTPUT.=('<p>Thank you for your donation.<br><b>Michael Kunze aka MrSpock</b></p>');

?>
