<?php
if(isset($var['AdminCreateGameID']) && $var['AdminCreateGameID'] !== false)
	$gameID = $var['AdminCreateGameID'];
else
	$gameID = $player->getGameID();

if(isset($var['AdminCreateGameID']) && $var['AdminCreateGameID'] !== false)
	$adminCreate = true;
else
	$adminCreate = false;

$file = ';SMR1.6 Sectors File v 1.03
[Races]
; Name = ID' . EOL;
$races =& Globals::getRaces();
foreach($races as &$race)
{
	$file.=inify($race['Race Name']).'='.$race['Race ID'] . EOL;
} unset($race);

$file.='[Goods]
; ID = Name, BasePrice' . EOL;
$goods =& Globals::getGoods();
foreach($goods as &$good)
{
	$file.=$good['ID'].'='.inify($good['Name']).','.$good['BasePrice'] . EOL;
} unset($good);

$file.='[Weapons]
; Weapon = Race,Cost,Shield,Armour,Accuracy,Power level,EMP (%),Align Restriction,Attack Restriction
; Align: 0=none, 1=good, 2=evil
; Attack: 0=none, 1=raid' . EOL;
$weapons =& SmrWeapon::getAllWeapons(Globals::getGameType($gameID));
foreach($weapons as &$weapon)
{
	$file.=inify($weapon->getName()).'='.inify($weapon->getRaceName()).','.$weapon->getCost().','.$weapon->getShieldDamage().','.$weapon->getArmourDamage().','.$weapon->getBaseAccuracy().','.$weapon->getPowerLevel().','.$weapon->getEmpDamage().','.$weapon->getBuyerRestriction().','.($weapon->isRaidWeapon()?'1':'0') . EOL;
} unset($weapon);

$file.='[ShipEquipment]
; Name = Cost' . EOL;
$hardwares =& Globals::getHardwareTypes();
foreach($hardwares as &$hardware)
{
	$file.=inify($hardware['Name']).'='.$hardware['Cost'] . EOL;
} unset($hardware);

$file.='[Ships]
; Name = Race,Cost,TPH,Hardpoints,Power,+Equipment (Optional),+Restrictions(Optional)
; Restrictions:Align(Integer)' . EOL;
$ships =& AbstractSmrShip::getAllBaseShips($gameID);
foreach($ships as &$ship)
{
	$file.=inify($ship['Name']).'='.Globals::getRaceName($ship['RaceID']).','.$ship['Cost'].','.$ship['Speed'].','.$ship['Hardpoint'].','.$ship['MaxPower'];
	if($ship['MaxHardware']>0)
	{
		$shipEquip=',ShipEquipment=';
		foreach($ship['MaxHardware'] as $hardwareID => $maxHardware)
		{
			$shipEquip.=$hardwares[$hardwareID]['Name'].'='.$maxHardware.';';
		}
		$file .= substr($shipEquip,0,-1);
		$file.=',Restrictions='.$ship['AlignRestriction'];
	}
	$file.= EOL;
} unset($ship);

$file.='[Locations]
; Name = +Sells' . EOL;
$locations =& SmrLocation::getAllLocations();
foreach($locations as &$location)
{
	$file.=inify($location->getName()).'=';
	$locSells='';
	if($location->isWeaponSold())
	{
		$locWeapons =& $location->getWeaponsSold();
		$locSells.='Weapons=';
		foreach($locWeapons as &$locWeapon)
		{
			$locSells.=$locWeapon->getName().';';
		} unset($locWeapon);
		$locSells = substr($locSells,0,-1).',';
	}
	if($location->isHardwareSold())
	{
		$locHardwares =& $location->getHardwareSold();
		$locSells.='ShipEquipment=';
		foreach($locHardwares as $locHardware)
		{
			$locSells.=$locHardware.';';
		}
		$locSells = substr($locSells,0,-1).',';
	}
	if($location->isShipSold())
	{
		$locShips =& $location->getShipsSold();
		$locSells.='Ships=';
		foreach($locShips as &$locShip)
		{
			$locSells.=$locShip['Name'].';';
		} unset($locShip);
		$locSells = substr($locSells,0,-1).',';
	}
	if($location->isBank())
	{
		$locSells.='Bank=,';
	}
	if($location->isBar())
	{
		$locSells.='Bar=,';
	}
	if($location->isHQ())
	{
		$locSells.='HQ=,';
	}
	if($location->isUG())
	{
		$locSells.='UG=,';
	}
	if($location->isFed())
	{
		$locSells.='Fed=,';
	}
	if($locSells!='')
		$file .= substr($locSells,0,-1);
	$file.= EOL;
} unset($location);

$file.='[Game]
Name='.inify(Globals::getGameName($gameID)).'
[Galaxies]
';
$galaxies =& SmrGalaxy::getGameGalaxies($gameID);
foreach ($galaxies as &$galaxy)
{
	$file .= $galaxy->getGalaxyID() . '=' . $galaxy->getWidth() . ',' . $galaxy->getHeight() . ',' . $galaxy->getGalaxyType() . ',' . inify($galaxy->getName()) . EOL;
} unset($galaxy);


foreach ($galaxies as &$galaxy)
{
	$sectors =& $galaxy->getSectors();
	foreach ($sectors as &$sector)
	{
		$file .= '[Sector=' . $sector->getSectorID() . ']' . EOL;
		
		if(!$sector->isVisited($player) && $adminCreate === false)
			continue;
		
		foreach($sector->getLinks() as $linkName => $link)
		{
			$file .= $linkName.'='.$link . EOL;
		}
		if($sector->hasWarp())
			$file .= 'Warp='.$sector->getWarp() . EOL;
		if(($adminCreate !== false && $sector->hasPort()) || is_object($player) && $sector->hasCachedPort($player))
		{
			if($adminCreate !== false)
				$port =& $sector->getPort();
			else
				$port =& $sector->getCachedPort($player);
			$file .= 'Port Level='.$port->getLevel() . EOL;
			$file .= 'Port Race=' . $port->getRaceID() . EOL;
			$portGoods =& $port->getGoods();
			if(count($portGoods['Sell'])>0)
			{
				$buyString = 'Buys=';
				foreach($portGoods['Sell'] as $goodID => $amount)
				{
					$buyString .= $goodID .',';
				}
				$file .= substr($buyString,0,-1) . EOL;
			}
			
			if(count($portGoods['Buy'])>0)
			{
				$sellString = 'Sells=';
				foreach($portGoods['Buy'] as $goodID => $amount)
				{
					$sellString .= $goodID .',';
				}
				$file .= substr($sellString,0,-1) . EOL;
			}
			unset($portGoods);
			unset($port);
		}
		if($sector->hasPlanet())
		{
			$file .= 'Planet=1' . EOL;
		}
		if($sector->hasLocation())
		{
			$locationsString= 'Locations=';
			$locations =& $sector->getLocations();
			foreach($locations as &$location)
			{
				$locationsString .= inify($location->getName()) . ',';
			} unset ($location);
			unset($locations);
			$file .= substr($locationsString,0,-1) . EOL;
		}
		if($sector->hasFriendlyForces($player))
		{
			$forcesString= 'FriendlyForces=';
			$friendlyForces =& $sector->getFriendlyForces();
			foreach($friendlyForces as &$forces)
			{
				$forcesString .= inify($forces->getOwner()->getName()) . '='.inify(Globals::getHardwareName(HARDWARE_MINE)).'='.$forces->getMines().';'.inify(Globals::getHardwareName(HARDWARE_COMBAT)).'='.$forces->getCDs().';'.inify(Globals::getHardwareName(HARDWARE_SCOUT)).'='.$forces->getSDs().',';
			} unset ($forces);
			unset($friendlyForces);
			$file .= substr($forcesString,0,-1) . EOL;
		}
	} unset($sector);
} unset($galaxy);

$size = strlen($file);

header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private',false);
header('Content-Type: application/force-download');
header('Content-Disposition: attachment; filename="'.Globals::getGameName($gameID).'.smr"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.$size);

echo $file;

release_lock();
exit;


function inify($text)
{
	return str_replace(',','',html_entity_decode($text));
}
?>