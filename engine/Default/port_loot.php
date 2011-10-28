<?php
$template->assign('PageTopic','Looting The Port');

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

$port =& $player->getSectorPort();

$container = create_container('port_loot_processing.php');

$boughtGoods =& $port->getVisibleGoodsBought($player);
foreach($boughtGoods as $goodID => &$boughtGood)
{
	$container['good_id'] = $goodID;
	$PHP_OUTPUT.=create_echo_form($container);

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center">' . $boughtGood['Name'] . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . $boughtGood['Amount'] . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . $boughtGood['BasePrice'] . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . $ship->getCargo($goodID) . '</td>');
	$PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="');

	$PHP_OUTPUT.=min($boughtGood['Amount'], $ship->getCargo($goodID));

	$PHP_OUTPUT.=('" size="4" id="InputFields" class="center"></td>');
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=create_submit('Loot');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('</form>');

}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>