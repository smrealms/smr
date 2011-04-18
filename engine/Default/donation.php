<?php

$template->assign('PageTopic','Donations');
$db->query('SELECT SUM(amount) as total_donation FROM account_donated WHERE time > ' . TIME . ' - (86400 * 90)');
if ($db->nextRecord())
	$template->assign('TotalDonation', $db->getField('total_donation'));

/*

// defines the pixel length of bar
$total_length = 400;

$components = array();
$components['<b>Processor</b><br /><span style="font-size:75%;">AMD ATHLON XP 2700+ Thoroughbred (2166MHz, 166/333 MHz) Socket A Boxed </span>'] = 299.99;
$components['<b>Motherboard</b><br /><span style="font-size:75%;">EPOX EP-8K9A3+ Athlon/Duron Socket A UDMA/133 RAID (4x) VIA KT400 DDR Sound USB2.0 LAN AGP 8x</span>'] = 136.99;
$components['<b>Harddisk</b><br /><span style="font-size:75%;">2 x SEAGATE Barracuda 7200.7 ST360014A, 60.0GB, 7200Rpm, UDMA/100</span>'] = 169.98;
$components['<b>Graphic Card</b><br /><span style="font-size:75%;">VGA 16MB AGP SiS305 AGP 2x Bulk</span>'] = 18.99;
$components['<b>CD-Rom</b><br /><span style="font-size:75%;">AOPEN 52x CD-ROM ATAPI Bulk</span>'] = 21.99;
$components['<b>Chassis</b><br /><span style="font-size:75%;">19" Chassis 300W</span>'] = 198.99;

$total_price = array_sum($components);

$PHP_OUTPUT.=('<p><table width="100%"><tr>');
$PHP_OUTPUT.=('<tr><th>Component</th><th>Price</th></tr>');

foreach ($components as $component => $price)
	$PHP_OUTPUT.=('<tr><td>'.$component.'</td><td align="right">' . number_format($price, 2) . ' �</td></tr>');

$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td align="right"><hr noshade width="100%"></td></tr>');
$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td align="right">' . number_format($total_price, 2) . ' �</td></tr>');

$PHP_OUTPUT.=('</table></p>');

$current_length = $total_length * $donations / $total_price;

$PHP_OUTPUT.=('<div align="center">');
if ($donations < $total_price) {

	$PHP_OUTPUT.=('<span style="font-size:75%;">Current Progress:<br /></span>');
	$PHP_OUTPUT.=('<img src="'.URL.'/images/progress_bar/bar_green_start.jpg">');

	for ($i = 0; $i < $current_length; $i++)
		$PHP_OUTPUT.=('<img src="'.URL.'/images/progress_bar/bar_green_mid.jpg">');

	for ($i = 0; $i < $total_length - $current_length; $i++)
		$PHP_OUTPUT.=('<img src="'.URL.'/images/progress_bar/bar_white_mid.jpg">');

	$PHP_OUTPUT.=('<img src="'.URL.'/images/progress_bar/bar_white_end.jpg">');
	$PHP_OUTPUT.=('<span style="font-size:60%;"><br />' . round(100 * $donations / $total_price) . ' %</span>');

} else
	$PHP_OUTPUT.=('WE DID IT! I'm going to buy the new hardware in a couple of days!');

$PHP_OUTPUT.=('</div>');
*/

?>