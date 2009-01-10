<?
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));

// create planet object
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());
$smarty->assign('PageTopic','PLANET : '.$planet->planet_name.' [SECTOR #'.$player->getSectorID().']');

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_planet_menue();

if ($planet->hasCurrentlyBuilding())
{
	$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
	$PHP_OUTPUT.=('<p>You are currently building:<br />');
	$currentlyBuilding = $planet->getCurrentlyBuilding();
	foreach($currentlyBuilding as $building)
	{
		$hours = floor($building['TimeRemaining'] / 3600);
		$minutes = floor(($building['TimeRemaining'] - $hours * 3600) / 60);
		$seconds = $building['TimeRemaining'] - $hours * 3600 - $minutes * 60;
	
		$PHP_OUTPUT.=($PLANET_BUILDINGS[$building['ConstructionID']]['Name'].' which will finish in ');
	
		if ($hours > 0)
		{
			if ($hours == 1)
				$PHP_OUTPUT.=($hours.' hour');
			else
				$PHP_OUTPUT.=($hours.' hours');
	
			if ($minutes > 0 && $seconds > 0)
				$PHP_OUTPUT.=(', ');
			elseif
				($minutes > 0 || $seconds > 0) $PHP_OUTPUT.=(' and ');
			else
				$PHP_OUTPUT.=('.');
		}
		if ($minutes > 0)
		{
			if ($minutes == 1)
				$PHP_OUTPUT.=($minutes.' minute');
			else
				$PHP_OUTPUT.=($minutes.' minutes');
			if ($seconds > 0)
				$PHP_OUTPUT.=(' and ');
		}
		if ($seconds > 0)
			if ($seconds == 1)
				$PHP_OUTPUT.=($seconds.' second');
			else
				$PHP_OUTPUT.=($seconds.' seconds');
	
		// esp. if no time left...
		if ($hours == 0 && $minutes == 0 && $seconds == 0)
			$PHP_OUTPUT.=('0 seconds');
	
		$container = array();
		$container['url'] = 'planet_construction_processing.php';
		$container['id'] = $building['ConstructionID'];
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=create_submit('Cancel');
		$PHP_OUTPUT.=('</form>');
	}
}
else
	$PHP_OUTPUT.=('<p>You are currently building: Nothing</p>');

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="3" border="0" class="standard">');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Type</th>');
$PHP_OUTPUT.=('<th>Description</th>');
$PHP_OUTPUT.=('<th>Current</th>');
$PHP_OUTPUT.=('<th>Cost</th>');
$PHP_OUTPUT.=('<th>Build</th>');
$PHP_OUTPUT.=('</tr>');

// get game speed
$db->query('SELECT * FROM game WHERE game_id = '.$player->getGameID());
if ($db->next_record())
	$game_speed = $db->f('game_speed');

$db2 = new SMR_DB();
$db->query('SELECT * FROM planet_construction ORDER BY construction_id');
while ($db->next_record())
{
	$construction_id			= $db->f('construction_id');
	$construction_name			= $db->f('construction_name');
	$construction_description	= $db->f('construction_description');

	$db2->query('SELECT * FROM planet_cost_credits WHERE construction_id = '.$construction_id);
	if ($db2->next_record())
		$cost = $db2->f('amount');

	/*$container = array();
	$container['url'] = 'planet_construction_processing.php';
	$container['construction_id'] = $construction_id;
	$container['cost'] = $cost;

	$PHP_OUTPUT.=create_echo_form($container);*/
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td>'.$construction_name.'</td>');
	$PHP_OUTPUT.=('<td>'.$construction_description.'</td>');
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=($planet->getBuilding($construction_id));
	$PHP_OUTPUT.=('/');
	$PHP_OUTPUT.=($planet->max_construction[$construction_id]);
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td>');
	$missing_good = false;
	$db2->query('SELECT * FROM planet_cost_good, good ' .
						'WHERE planet_cost_good.good_id = good.good_id AND ' .
							  'construction_id = '.$construction_id.' ' .
						'ORDER BY good.good_id');
	while ($db2->next_record())
	{
		$good_id	= $db2->f('good_id');
		$good_name	= $db2->f('good_name');
		$amount		= $db2->f('amount');

		if ($planet->getStockpile($good_id) < $amount)
		{
			$PHP_OUTPUT.=('<span style="color:red;">'.$amount.'-'.$good_name.', </span>');
			$missing_good = true;

		}
		else
			$PHP_OUTPUT.=($amount.'-'.$good_name.', ');

	}

	$missing_credits = false;
	if ($player->getCredits() < $cost)
	{
		$PHP_OUTPUT.=('<span style="color:red;">'.$cost.'-credits, </span>');
		$missing_credits = true;
	}
	else
		$PHP_OUTPUT.=($cost.'-credits, ');

	$db2->query('SELECT * FROM planet_cost_time WHERE construction_id = '.$construction_id);
	if ($db2->next_record())
		$PHP_OUTPUT.=(($db2->f('amount') / 3600 / $game_speed) . '-hours');

	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td>');
	if (!$missing_good && !$missing_credits && !$planet->isCurrentlyBuilding() && $planet->getBuilding($construction_id) < $planet->max_construction[$construction_id])
	{
		$container = array();
		$container['url'] = 'planet_construction_processing.php';
		$container['construction_id'] = $construction_id;
		$container['cost'] = $cost;
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=create_submit('Build');
		$PHP_OUTPUT.=('</form>');
	}
	else
		$PHP_OUTPUT.=('&nbsp;');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');
	//$PHP_OUTPUT.=('</form>');

}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('<p>Your stockpile contains :</p>');
$PHP_OUTPUT.=('<ul>');
foreach ($planet->getStockpile() as $id => $amount)
{
	if ($amount > 0)
	{

		$db->query('SELECT * FROM good WHERE good_id = '.$id);
		if ($db->next_record())
			$PHP_OUTPUT.=('<li>' . $db->f('good_name') . ': '.$amount.'</li>');

	}
}
$PHP_OUTPUT.=('</ul>');

?>