<?
$smarty->assign('DISABLE_AJAX_CONTENT_REFRESH',true);//Stop form fields being overwritten.
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);
require_once(get_file_loc('SmrPort.class.inc'));
$smarty->assign('PageTopic','LOOT');

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=create_table();
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th align="center">Good</th>');
$PHP_OUTPUT.=('<th align="center">Supply/Demand</th>');
$PHP_OUTPUT.=('<th align="center">Base Price</th>');
$PHP_OUTPUT.=('<th align="center">Amount on Ship</th>');
$PHP_OUTPUT.=('<th align="center">Amount to Trade</th>');
$PHP_OUTPUT.=('<th align="center">Action</th>');
$PHP_OUTPUT.=('</tr>');

// and a port object
$port =& SmrPort::getPort(SmrSession::$game_id,$player->getSectorID());

$container = array();
$container['url'] = 'port_loot_processing.php';

$want = 'Buy';
$db->query('SELECT * FROM port, port_has_goods, good WHERE port.game_id = port_has_goods.game_id AND ' .
                                                         'port.sector_id = port_has_goods.sector_id AND ' .
                                                         'port_has_goods.good_id = good.good_id AND ' .
                                                         'port.sector_id = '.$sector->getSectorID().' AND ' .
                                                         'port_has_goods.transaction_type = ' . $db->escape_string($want, true) . ' AND ' .
                                                         'port.game_id = '.SmrSession::$game_id.' ' .
                                                   'ORDER BY good.good_id');


while ($db->nextRecord())
{

   $good_id = $db->getField('good_id');
   $good_name = $db->getField('good_name');
   $good_class = $db->getField('good_class');
	$portGood = $port->getGood($good_id);
   if ($portGood['BasePrice'] == 0) continue;
	if ($player->getAlignment() > -100 && ($good_id == 5 || $good_id == 9 || $good_id == 12)) continue;
   $container['good_id'] = $good_id;
   $container['good_name'] = $good_name;
   $container['good_class'] = $good_class;
   $PHP_OUTPUT.=create_echo_form($container);

   $PHP_OUTPUT.=('<tr>');
   $PHP_OUTPUT.=('<td align="center">'.$good_name.'</td>');
   $PHP_OUTPUT.=('<td align="center">' . $portGood['Amount'] . '</td>');
   $PHP_OUTPUT.=('<td align="center">' . $portGood['BasePrice'] . '</td>');
   $PHP_OUTPUT.=('<td align="center">' . $ship->getCargo($good_id) . '</td>');
   $PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="');

   if ($portGood['TransactionType'] == 'Sell') {

       if ($portGood['Amount'] < $ship->getCargo($good_id))
           $PHP_OUTPUT.=($portGood['Amount']);
       else
           $PHP_OUTPUT.=($ship->getCargo($good_id));

   } else {

       if ($portGood['Amount'] < $ship->getEmptyHolds())
           $PHP_OUTPUT.=($portGood['Amount']);
       else
           $PHP_OUTPUT.=($ship->getEmptyHolds());

   }

   $PHP_OUTPUT.=('" size="4" id="InputFields" style="text-align:center;"></td>');
   $PHP_OUTPUT.=('<td align="center">');
   $PHP_OUTPUT.=create_submit('Loot');
   $PHP_OUTPUT.=('</td>');
   $PHP_OUTPUT.=('</tr>');
   $PHP_OUTPUT.=('</form>');

}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>