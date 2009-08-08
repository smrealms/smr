<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));

// create planet object
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());
$template->assign('PageTopic','PLANET : '.$planet->planet_name.' [SECTOR #'.$player->getSectorID().']');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_planet_menue();

$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
if ($planet->hasCurrentlyBuilding())
{
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
		$container['construction_id'] = $building['ConstructionID'];
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=create_submit('Cancel');
		$PHP_OUTPUT.=('</form>');
	}
}
else
	$PHP_OUTPUT.=('<p>You are currently building: Nothing</p>');

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<table class="standard">');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Type</th>');
$PHP_OUTPUT.=('<th>Description</th>');
$PHP_OUTPUT.=('<th>Current</th>');
$PHP_OUTPUT.=('<th>Cost</th>');
$PHP_OUTPUT.=('<th>Build</th>');
$PHP_OUTPUT.=('</tr>');

$GOODS =& Globals::getGoods();
foreach($PLANET_BUILDINGS as $planetBuilding)
{
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td>'.$planetBuilding['Name'].'</td>');
	$PHP_OUTPUT.=('<td>'.$planetBuilding['Description'].'</td>');
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=($planet->getBuilding($planetBuilding['ConstructionID']));
	$PHP_OUTPUT.=('/');
	$PHP_OUTPUT.=($planet->max_construction[$planetBuilding['ConstructionID']]);
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td>');
	$missing_good = false;
	foreach($planetBuilding['Goods'] as $goodID => $amount)
	{
		if ($planet->getStockpile($goodID) < $amount)
		{
			$PHP_OUTPUT.=('<span style="color:red;">'.$amount.'-'.$GOODS[$goodID]['Name'].', </span>');
			$missing_good = true;
		}
		else
			$PHP_OUTPUT.=($amount.'-'.$GOODS[$goodID]['Name'].', ');
	}

	$missing_credits = false;
	if ($player->getCredits() < $planetBuilding['Credit Cost'])
	{
		$PHP_OUTPUT.=('<span style="color:red;">'.$planetBuilding['Credit Cost'].'-credits, </span>');
		$missing_credits = true;
	}
	else
		$PHP_OUTPUT.=($planetBuilding['Credit Cost'].'-credits, ');

	$PHP_OUTPUT.=(($planetBuilding['Build Time'] / 3600) / Globals::getGameSpeed($player->getGameID()) . '-hours');

	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td>');
	if (!$missing_good && !$missing_credits && !$planet->hasCurrentlyBuilding() && $planet->getBuilding($planetBuilding['ConstructionID']) < $planet->max_construction[$planetBuilding['ConstructionID']])
	{
		$container = array();
		$container['url'] = 'planet_construction_processing.php';
		$container['construction_id'] = $planetBuilding['ConstructionID'];
		$container['cost'] = $planetBuilding['Credit Cost'];
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
		$PHP_OUTPUT.=('<li>' . $GOODS[$id]['Name'] . ': '.$amount.'</li>');

	}
}
$PHP_OUTPUT.=('</ul>');

?>