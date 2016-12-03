<?php

// defines the pixel length of bar
$total_length = 400;

print_topic("DONATIONS");
print("<p style=\"width:60%; text-align:justify;\">Hello,<br /><br />If you enjoy the game i'd like to ask you to donate a few bucks to
help keep it alive. Every little donation can help me. Beside for the cost for the server
i invest most of my free time into this game to make it better. So if you are able
to give me a financial aid i really would appreciate it.</p>");
$db->query("SELECT SUM(amount) as total_donation FROM account_donated WHERE time > " . time() . " - (60 * 60 * 24 * 90)");
if ($db->next_record())
	$total_donation = $db->f("total_donation");

print("Current donation rate is: $" . number_format($total_donation / 12, 2) . " per week (within last 3 months).");

print('<br /><br /><br />');

// Begin PayPal Logo
print("<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">");
print("<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">");
print("<input type=\"hidden\" name=\"business\" value=\"mrspock@smrealms.de\">");
print("<input type=\"hidden\" name=\"item_name\" value=\"Support development with money.\">");
print("<input type=\"hidden\" name=\"item_number\" value=\"" . $account->account_id . "\">");
print("<input type=\"hidden\" name=\"no_shipping\" value=\"1\">");
print("<input type=\"hidden\" name=\"no_note\" value=\"1\">");
print("<input type=\"hidden\" name=\"currency_code\" value=\"USD\">");
print("<input type=\"hidden\" name=\"tax\" value=\"0\">");
print("<input type=\"hidden\" name=\"lc\" value=\"US\">");
print("<input type=\"hidden\" name=\"bn\" value=\"PP-DonationsBF\">");
print("<input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/x-click-but21.gif\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">");
print("<img alt=\"\" border=\"0\" src=\"https://www.paypal.com/en_US/i/scr/pixel.gif\" width=\"1\" height=\"1\">");
print("</form>");
// End PayPal Logo -->

// get donations from db

/*

$components = array();
$components["<b>Processor</b><br><span style=\"font-size:75%;\">AMD ATHLON XP 2700+ Thoroughbred (2166MHz, 166/333 MHz) Socket A Boxed </span>"] = 299.99;
$components["<b>Motherboard</b><br><span style=\"font-size:75%;\">EPOX EP-8K9A3+ Athlon/Duron Socket A UDMA/133 RAID (4x) VIA KT400 DDR Sound USB2.0 LAN AGP 8x</span>"] = 136.99;
$components["<b>Harddisk</b><br><span style=\"font-size:75%;\">2 x SEAGATE Barracuda 7200.7 ST360014A, 60.0GB, 7200Rpm, UDMA/100</span>"] = 169.98;
$components["<b>Graphic Card</b><br><span style=\"font-size:75%;\">VGA 16MB AGP SiS305 AGP 2x Bulk</span>"] = 18.99;
$components["<b>CD-Rom</b><br><span style=\"font-size:75%;\">AOPEN 52x CD-ROM ATAPI Bulk</span>"] = 21.99;
$components["<b>Chassis</b><br><span style=\"font-size:75%;\">19\" Chassis 300W</span>"] = 198.99;

$total_price = array_sum($components);

print("<p><table width=\"100%\"><tr>");
print("<tr><th>Component</th><th>Price</th></tr>");

foreach ($components as $component => $price)
	print("<tr><td>$component</td><td align=\"right\">" . number_format($price, 2) . " �</td></tr>");

print("<tr><td>&nbsp;</td><td align=\"right\"><hr noshade width=\"100%\"></td></tr>");
print("<tr><td>&nbsp;</td><td align=\"right\">" . number_format($total_price, 2) . " �</td></tr>");

print("</table></p>");

$current_length = $total_length * $donations / $total_price;

print("<div align=\"center\">");
if ($donations < $total_price) {

	print("<span style=\"font-size:75%;\">Current Progress:<br></span>");
	print("<img src=\"".URL."/images/progress_bar/bar_green_start.jpg\">");

	for ($i = 0; $i < $current_length; $i++)
		print("<img src=\"".URL."/images/progress_bar/bar_green_mid.jpg\">");

	for ($i = 0; $i < $total_length - $current_length; $i++)
		print("<img src=\"".URL."/images/progress_bar/bar_white_mid.jpg\">");

	print("<img src=\"".URL."/images/progress_bar/bar_white_end.jpg\">");
	print("<span style=\"font-size:60%;\"><br>" . round(100 * $donations / $total_price) . " %</span>");

} else
	print("WE DID IT! I'm going to buy the new hardware in a couple of days!");

print("</div>");
*/
print("<p>&nbsp;</p>");
print("<p>Thank you for your donation.<br><b>Michael Kunze aka MrSpock</b></p>");

?>
