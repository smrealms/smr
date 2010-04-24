<?php
		require_once(get_file_loc("smr_port.inc"));
$player->get_relations();
print_topic("NEGOTIATE PRICE");
require_once("shop_goods.inc");
// creates needed objects
$port = new SMR_PORT($player->sector_id, SmrSession::$game_id);

// get values from request
$good_id = $var["good_id"];

if ($var["bargain_price"] > 0) {

	$bargain_price = $var["bargain_price"];

	print("<p>I can't accept your offer. It's still too ");
	if ($port->transaction[$good_id] == 'Sell')
		print("high");
	elseif ($port->transaction[$good_id] == 'Buy')
		print("low");
	print(".</p>");

	// lose relations for bad bargain
	$relation_modifier = round($var["amount"] / 30);
	if ($relation_modifier > 10)
		$relation_modifier = 10;

	$player->relations[$port->race_id] -= $relation_modifier;
	$player->update();

} else
	$bargain_price = $var["offered_price"];

print("<p>I would ");
if ($port->transaction[$good_id] == 'Sell')
	print("buy ");
elseif ($port->transaction[$good_id] == 'Buy')
	print("offer you ");
print($var["amount"] . " pcs. of " . $var["good_name"] . " for " . $var["offered_price"] . " credits!<br>");
print("Note: In order to maximize your experience you have to bargain with the port owner, unless you have maxmium relations (1000) with that race, which gives full experience without the need to bargain.</p>");

$container = array();
$container["url"] = "shop_goods_processing.php";

transfer("amount");
transfer("good_id");
transfer("good_name");
transfer("good_class");
transfer("good_distance");
transfer("offered_price");
transfer("ideal_price");
transfer("number_of_bargains");
transfer("overall_number_of_bargains");

print_form($container);
$relations = $player->relations[$port->race_id] + $player->relations_global_rev[$port->race_id];
$value = round(pow( ($relations / 1000),10 ) );
//gives value 0-1
$ideal_price = get_ideal_price();
$offered_price = get_offered_price();
//print("$ideal_price, $offered_price,");
$show_price = abs($offered_price - $ideal_price);
//print("$show_price,");
$show_price = $show_price * $value;
//print("$show_price,");
if ($port->transaction[$good_id] == 'Sell')
	$show_price = $bargain_price + $show_price;
else
	$show_price = $bargain_price - $show_price;
//print("$show_price");
print("<input type=\"text\" name=\"bargain_price\" value=\"$show_price\" id=\"InputFields\" style=\"width:75;text-align:center;vertical-align:middle;\">&nbsp;");
print('<!-- here are all information that are needed to calculate the ideal price. if you know how feel free to create a trade calculator -->');
print('<!--('.$var['amount'].':'.$port->base_price[$good_id].':'.$var['good_distance'].':'.$port->amount[$good_id].':'.$port->max_amount[$good_id].':'.$relations.':'.$port->level.')-->');
print_submit("Bargain (1)");
print("</form>");

print("<SCRIPT LANGUAGE=\"javascript\">\n");
print("window.document.FORM.bargain_price.select();\n");
print("window.document.FORM.bargain_price.focus();\n");
print("</SCRIPT>\n");

print("<p>&nbsp;</p>");

print("<h2>Or do you want:</h2>");

print_form(create_container("skeleton.php", "shop_goods.php"));
print_submit("Select a different good");
print("</form>");
print("<br><br>");
print_form(create_container("skeleton.php", "current_sector.php"));
print_submit("Leave Port");
print("</form>");

?>