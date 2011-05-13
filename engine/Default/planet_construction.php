<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));

// create planet object
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

include(get_file_loc('menue.inc'));
create_planet_menue();

$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
$PHP_OUTPUT.=('<p>You are currently building: ');
if ($planet->hasCurrentlyBuilding())
{
	$PHP_OUTPUT.=('<br />');
	$currentlyBuilding = $planet->getCurrentlyBuilding();
	foreach($currentlyBuilding as $building)
	{
		$PHP_OUTPUT.=$PLANET_BUILDINGS[$building['ConstructionID']]['Name'].' which will finish in ';
	
		$PHP_OUTPUT.=format_time($building['TimeRemaining']);
	
		$container = array();
		$container['url'] = 'planet_construction_processing.php';
		$container['construction_id'] = $building['ConstructionID'];
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=create_submit('Cancel');
		$PHP_OUTPUT.=('</form>');
	}
}
else
{
	$PHP_OUTPUT.='Nothing';
}
$PHP_OUTPUT.=('</p>');

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
	$PHP_OUTPUT.=($planet->getMaxBuildings($planetBuilding['ConstructionID']));
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td>');
	$missing_good = false;
	foreach($planetBuilding['Goods'] as $goodID => $amount)
	{
		if ($planet->getStockpile($goodID) < $amount)
		{
			$PHP_OUTPUT.=('<span class="red">'.$amount.'-'.$GOODS[$goodID]['Name'].', </span>');
			$missing_good = true;
		}
		else
			$PHP_OUTPUT.=($amount.'-'.$GOODS[$goodID]['Name'].', ');
	}

	$missing_credits = false;
	if ($player->getCredits() < $planetBuilding['Credit Cost'])
	{
		$PHP_OUTPUT.=('<span class="red">'.$planetBuilding['Credit Cost'].'-credits, </span>');
		$missing_credits = true;
	}
	else
		$PHP_OUTPUT.=($planetBuilding['Credit Cost'].'-credits, ');

	$PHP_OUTPUT.= format_time(($planetBuilding['Build Time']) / Globals::getGameSpeed($player->getGameID()));

	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td>');
	if (!$missing_good && !$missing_credits && !$planet->hasCurrentlyBuilding() && $planet->getBuilding($planetBuilding['ConstructionID']) < $planet->getMaxBuildings($planetBuilding['ConstructionID']))
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
		$PHP_OUTPUT.=('<li><img src="' . $GOODS[$id]['ImageLink'] . '" title="' . $GOODS[$id]['Name'] . '" alt="' . $GOODS[$id]['Name'] . '" />: '.$amount.'</li>');

	}
}
$PHP_OUTPUT.=('</ul>');

?>