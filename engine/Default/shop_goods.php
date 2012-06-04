<?php
// include helper file
require_once('shop_goods.inc');

// create object from port we can work with
$port =& $player->getSectorPort();

$tradeable = checkPortTradeable($port,$player);
if($tradeable!==true)
	create_error($tradeable);

$portRelations = Globals::getRaceRelations($player->getGameID(),$port->getRaceID());
$relations = $player->getRelation($port->getRaceID());

// topic
$template->assign('PageTopic','Port In Sector #'.$player->getSectorID());

$container = create_container('skeleton.php', 'council_list.php');
$container['race_id'] = $port->getRaceID();

$PHP_OUTPUT.=('<p>This is a level '.$port->getLevel().' port and run by the ' . create_link($container, $player->getColouredRaceName($port->getRaceID())) . '.<br />');
$PHP_OUTPUT.=('Your relations with them are ' . get_colored_text($relations, $relations) . '.</p>');

$PHP_OUTPUT.=('<p>&nbsp;</p>');
$account->log(LOG_TYPE_TRADING, 'Player examines port', $player->getSectorID());
//The player is sent here after trading and sees this if his offer is accepted.
//You have bought/sold 300 units of Luxury Items for 1738500 credits. For your excellent trading skills you receive 220 experience points!
if (!empty($var['traded_xp']) ||
	!empty($var['traded_amount']) ||
	!empty($var['traded_good']) ||
	!empty($var['traded_credits']) ||
	!empty($var['traded_transaction'])) {

	$PHP_OUTPUT.=('<p>You have just ' . $var['traded_transaction'] . ' <span class="yellow">' . $var['traded_amount'] . '</span> units ');
	$PHP_OUTPUT.=('of <span class="yellow">' . $var['traded_good'] . '</span> for ');
	$PHP_OUTPUT.=('<span class="creds">' . $var['traded_credits'] . '</span> credits.<br />');
	if ($var['traded_xp'] > 0)
		$PHP_OUTPUT.=('<p>For your excellent trading skills you have gained <span class="exp">' . $var['traded_xp'] . '</span> experience points!</p>');

// test if we are searched. (but only if we hadn't a previous trade here
}
elseif ($player->getLastPort() != $player->getSectorID())
{
	$allGoods = $port->getGoodsAll();

	$base_chance = 15;
	if(isset($allGoods[5]))
		$base_chance -= 4;
	if(isset($allGoods[9]))
		$base_chance -= 4;
	if(isset($allGoods[12]))
		$base_chance -= 4;

	if ($ship->getShipTypeID() == 23 || $ship->getShipTypeID() == 24 || $ship->getShipTypeID() == 25)
		$base_chance -= 4;

	$rand = mt_rand(1, 100);
	if ($rand <= $base_chance)
	{
		$player->increaseHOF(1,array('Trade','Search','Total'), HOF_PUBLIC);
		if ($ship->hasCargo(5) || $ship->hasCargo(9) || $ship->hasCargo(12))
		{
			$player->increaseHOF(1,array('Trade','Search','Caught','Number Of Times'), HOF_PUBLIC);
			//find the fine
			//get base for ports that dont happen to trade that good
			$query = new SmrMySqlDatabase();
			$GOODS = Globals::getGoods();
			$fine = $totalFine = $port->getLevel() * (($ship->getCargo(5) * $GOODS[5]['BasePrice']) +
									($ship->getCargo(9) * $GOODS[9]['BasePrice']) +
									($ship->getCargo(12) * $GOODS[12]['BasePrice']));
			$player->increaseHOF($ship->getCargo(5)+$ship->getCargo(9)+$ship->getCargo(12),array('Trade','Search','Caught','Goods Confiscated'), HOF_PUBLIC);
			$player->increaseHOF($totalFine,array('Trade','Search','Caught','Amount Fined'), HOF_PUBLIC);
			if($fine > $player->getCredits())
			{
				$fine -= $player->getCredits();
				$player->decreaseCredits($player->getCredits());
				if ($fine > 0)
				{
					// because credits is 0 it will take money from bank
					$player->decreaseBank(min($fine,$player->getBank()));
					// leave insurance
					if ($player->getBank() < 5000)
						$player->setBank(5000);
				}
			}
			else
			{
				$player->decreaseCredits($fine);
			}

			$PHP_OUTPUT.=('<span class="red">The Federation searched your ship and illegal goods were found!</span><br />');
			$PHP_OUTPUT.=('<span class="red">All illegal goods have been removed from your ship and you have been fined ' . number_format($totalFine) . ' credits</span>');

			//lose align and the good your carrying along with money
			$player->decreaseAlignment(5);

			$ship->setCargo(5,0);
			$ship->setCargo(9,0);
			$ship->setCargo(12,0);
			$account->log(LOG_TYPE_TRADING, 'Player gets caught with illegals', $player->getSectorID());

		}
		else
		{
			$player->increaseHOF(1,array('Trade','Search','Times Found Innocent'), HOF_PUBLIC);
			$PHP_OUTPUT.=('<span class="blue">The Federation searched your ship and no illegal goods where found!</span>');
			$player->increaseAlignment(1);
			$account->log(LOG_TYPE_TRADING, 'Player gains alignment at port', $player->getSectorID());
		}
	}
}
$player->setLastPort($player->getSectorID());
//update controlled in db
$player->controlled = $player->getSectorID();
$boughtGoods = $port->getVisibleGoodsBought($player);
if (!empty($boughtGoods))
{
	$PHP_OUTPUT.=('<h2>The port sells you the following:</h2>');
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Good</th>');
	$PHP_OUTPUT.=('<th align="center">Supply</th>');
	$PHP_OUTPUT.=('<th align="center">Base Price</th>');
	$PHP_OUTPUT.=('<th align="center">Amount on Ship</th>');
	$PHP_OUTPUT.=('<th align="center">Amount to Trade</th>');
	$PHP_OUTPUT.=('<th align="center">Action</th>');
	$PHP_OUTPUT.=('</tr>');

	$container = array();
	$container['url'] = 'shop_goods_processing.php';

	foreach ($boughtGoods as $good)
	{
		$container['good_id'] = $good['ID'];
		
		$PHP_OUTPUT.=create_echo_form($container);
			
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$good['Name'].'</td>');
		$PHP_OUTPUT.=('<td align="center">' . $good['Amount'] . '</td>');
		$PHP_OUTPUT.=('<td align="center">' . $good['BasePrice'] . '</td>');
		$PHP_OUTPUT.=('<td align="center">' . $ship->getCargo($good['ID']) . '</td>');
		$PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="');

		if ($good['Amount'] < $ship->getEmptyHolds())
			$PHP_OUTPUT.=($good['Amount']);
		else
			$PHP_OUTPUT.=($ship->getEmptyHolds());

		$PHP_OUTPUT.=('" size="4" id="InputFields" class="center"></td>');
		$PHP_OUTPUT.=('<td align="center">');
		$PHP_OUTPUT.=create_submit($good['TransactionType']);
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		
		$PHP_OUTPUT.=('</form>');


	}

	$PHP_OUTPUT.=('</table>');

	$PHP_OUTPUT.=('<p>&nbsp;</p>');

}

$soldGoods = $port->getVisibleGoodsSold($player);
if (!empty($soldGoods))
{
	$PHP_OUTPUT.=('<h2>The port would buy the following:</h2>');
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Good</th>');
	$PHP_OUTPUT.=('<th align="center">Demand</th>');
	$PHP_OUTPUT.=('<th align="center">Base Price</th>');
	$PHP_OUTPUT.=('<th align="center">Amount on Ship</th>');
	$PHP_OUTPUT.=('<th align="center">Amount to Trade</th>');
	$PHP_OUTPUT.=('<th align="center">Action</th>');
	$PHP_OUTPUT.=('</tr>');

	$container = array();
	$container['url'] = 'shop_goods_processing.php';

	foreach ($soldGoods as $good)
	{
		$container['good_id'] = $good['ID'];
		$PHP_OUTPUT.=create_echo_form($container);

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$good['Name'].'</td>');
		$PHP_OUTPUT.=('<td align="center">' . $good['Amount'] . '</td>');
		$PHP_OUTPUT.=('<td align="center">' . $good['BasePrice'] . '</td>');
		$PHP_OUTPUT.=('<td align="center">' . $ship->getCargo($good['ID']) . '</td>');
		$PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="');

		if ($good['Amount'] < $ship->getCargo($good['ID']))
			$PHP_OUTPUT.=($good['Amount']);
		else
			$PHP_OUTPUT.=$ship->getCargo($good['ID']);

		$PHP_OUTPUT.=('" size="4" id="InputFields" class="center"></td>');
		$PHP_OUTPUT.=('<td align="center">');
		$PHP_OUTPUT.=create_submit($good['TransactionType']);
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('</form>');

	}

	$PHP_OUTPUT.=('</table>');

	$PHP_OUTPUT.=('<p>&nbsp;</p>');

}

$PHP_OUTPUT.=('<h2>Or do you want to:</h2>');

$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'current_sector.php'));
$PHP_OUTPUT.=create_submit('Leave Port');
$PHP_OUTPUT.=('<form>');

?>