<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menu($planet);

$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
$PHP_OUTPUT.=('<p>You are currently building: ');
if ($planet->hasCurrentlyBuilding()) {
	$PHP_OUTPUT.=('<br />');
	$currentlyBuilding = $planet->getCurrentlyBuilding();
	foreach($currentlyBuilding as $building) {
		$PHP_OUTPUT.=$PLANET_BUILDINGS[$building['ConstructionID']]['Name'].' which will finish in ';
	
		$PHP_OUTPUT.=format_time($building['TimeRemaining']);
	
		$container = create_container('planet_construction_processing.php');
		$container['construction_id'] = $building['ConstructionID'];
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=create_submit('Cancel');
		$PHP_OUTPUT.=('</form>');
	}
}
else {
	$PHP_OUTPUT.='Nothing!';
}
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<table class="standard">');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th></th>');
$PHP_OUTPUT.=('<th>Description</th>');
$PHP_OUTPUT.=('<th>Current</th>');
$PHP_OUTPUT.=('<th>Cost</th>');
$PHP_OUTPUT.=('<th>Build</th>');
$PHP_OUTPUT.=('</tr>');

$GOODS =& Globals::getGoods();
foreach($PLANET_BUILDINGS as $planetBuilding) {
	if ($planet->getMaxBuildings($planetBuilding['ConstructionID']) > 0) {
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td><img class="tooltip" id="'.$planetBuilding['Name']
			.'_tip" src="'.$planetBuilding['Image'].'"  width="16" height="16" alt="" title="'
			.$planetBuilding['Name'].'"/></td>');
		$PHP_OUTPUT.=('<td>'.$planetBuilding['Name'].': '.$planetBuilding['Description'].'</td>');
		$PHP_OUTPUT.=('<td align="center">');
		$PHP_OUTPUT.=($planet->getBuilding($planetBuilding['ConstructionID']));
		$PHP_OUTPUT.=('/');
		$PHP_OUTPUT.=($planet->getMaxBuildings($planetBuilding['ConstructionID']));
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td>');
		foreach($planetBuilding['Goods'] as $goodID => $amount) {
			if ($planet->getStockpile($goodID) < $amount) {
				$PHP_OUTPUT.=('<span class="red">'.$amount.'-'.$GOODS[$goodID]['Name'].', </span>');
			}
			else
				$PHP_OUTPUT.=($amount.'-'.$GOODS[$goodID]['Name'].', ');
		}
		
		if ($player->getCredits() < $planetBuilding['Credit Cost'][$planet->getTypeID()]) {
			$PHP_OUTPUT.=('<span class="red">'.number_format($planetBuilding['Credit Cost'][$planet->getTypeID()]).'-credits, </span>');
		}
		else
			$PHP_OUTPUT.=number_format($planetBuilding['Credit Cost'][$planet->getTypeID()]).'-credits, ';

		$PHP_OUTPUT.= format_time($planet->getConstructionTime($planetBuilding['ConstructionID']) - TIME);

		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td>');
		if ($planet->canBuild($player, $planetBuilding['ConstructionID'])===true) {
			$container = array();
			$container['url'] = 'planet_construction_processing.php';
			$container['construction_id'] = $planetBuilding['ConstructionID'];
			$PHP_OUTPUT.=create_echo_form($container);
			$PHP_OUTPUT.=create_submit('Build');
			$PHP_OUTPUT.=('</form>');
		}
		else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
	}
	//$PHP_OUTPUT.=('</form>');

}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('<p>Your stockpile contains:');
$stockpile = false;


foreach ($planet->getStockpile() as $id => $amount) {
	if ($amount > 0) {
		if (!$stockpile) {
			$PHP_OUTPUT.=('</p><ul>');
			$stockpile = true;
		}
		$PHP_OUTPUT.=('<li><img src="' . $GOODS[$id]['ImageLink'] . '" title="' . $GOODS[$id]['Name'] . '" alt="' . $GOODS[$id]['Name'] . '" />&nbsp;' . $GOODS[$id]['Name'] . ': '.$amount.'</li>');
	}
}
if ($stockpile)
	$PHP_OUTPUT.=('</ul>');
else
	$PHP_OUTPUT.=(' Nothing!</p>');

?>