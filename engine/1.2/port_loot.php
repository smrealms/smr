<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);
		require_once(get_file_loc("smr_port.inc"));
$player->get_relations();
print_topic("LOOT");

print("<div align=\"center\">");
print_table();
print("<tr>");
print("<th align=\"center\">Good</th>");
print("<th align=\"center\">Supply/Demand</th>");
print("<th align=\"center\">Base Price</th>");
print("<th align=\"center\">Amount on Ship</th>");
print("<th align=\"center\">Amount to Trade</th>");
print("<th align=\"center\">Action</th>");
print("</tr>");

// and a port object
$port = new SMR_PORT($player->sector_id, SmrSession::$game_id);

$relations = $player->relations[$port->race_id] + $player->relations_global[$port->race_id];
if (empty($relations)) $relations = 0;

$container = array();
$container["url"] = "port_loot_processing.php";

$want = "Buy";
$db->query("SELECT * FROM port, port_has_goods, good WHERE port.game_id = port_has_goods.game_id AND " .
                                                         "port.sector_id = port_has_goods.sector_id AND " .
                                                         "port_has_goods.good_id = good.good_id AND " .
                                                         "port.sector_id = $sector->sector_id AND " .
                                                         "port_has_goods.transaction = " . format_string($want, true) . " AND " .
                                                         "port.game_id = ".SmrSession::$game_id." " .
                                                   "ORDER BY good.good_id");


while ($db->next_record()) {

   $good_id = $db->f("good_id");
   $good_name = $db->f("good_name");
   $good_class = $db->f("good_class");

   if ($port->base_price[$good_id] == 0) continue;
	if ($player->alignment > -100 && ($good_id == 5 || $good_id == 9 || $good_id == 12)) continue;
   $container["good_id"] = $good_id;
   $container["good_name"] = $good_name;
   $container["good_class"] = $good_class;
   print_form($container);

   print("<tr>");
   print("<td align=\"center\">$good_name</td>");
   print("<td align=\"center\">" . $port->amount[$good_id] . "</td>");
   print("<td align=\"center\">" . $port->base_price[$good_id] . "</td>");
   print("<td align=\"center\">" . $ship->cargo[$good_id] . "</td>");
   print("<td align=\"center\"><input type=\"text\" name=\"amount\" value=\"");

   if ($port->transaction[$good_id] == 'Sell') {

       if ($port->amount[$good_id] < $ship->cargo[$good_id])
           print($port->amount[$good_id]);
       else
           print($ship->cargo[$good_id]);

   } else {

       if ($port->amount[$good_id] < $ship->cargo_left)
           print($port->amount[$good_id]);
       else
           print($ship->cargo_left);

   }

   print("\" size=\"4\" id=\"InputFields\" style=\"text-align:center;\"></td>");
   print("<td align=\"center\">");
   print_submit("Loot");
   print("</td>");
   print("</tr>");
   print("</form>");

}

print("</table>");
print("</div>");

?>