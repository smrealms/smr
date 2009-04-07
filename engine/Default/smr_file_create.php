<?php
$gameID = $player->getGameID();

$file = ';SMR1.6 Sectors File v 1.01
[Game]
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
		
		if(!$sector->isVisited($player))
			continue;
		
		foreach($sector->getLinks() as $linkName => $link)
		{
			$file .= $linkName.'='.$link . EOL;
		}
		if($sector->hasCachedPort($player))
		{
			$port =& $sector->getCachedPort($player);
			$file .= 'Port Level='.$port->getLevel() . EOL;
			$file .= 'Port Race=' . $port->getRaceID() . EOL;
			$portGoods =& $port->getGoods();
			if(count($portGoods['Buy'])>0)
			{
				$buyString = 'Buys=';
				foreach($portGoods['Buy'] as $goodID => $amount)
				{
					$buyString .= $goodID .',';
				}
				$file .= substr($buyString,0,-1) . EOL;
			}
			
			if(count($portGoods['Sell'])>0)
			{
				$sellString = 'Sells=';
				foreach($portGoods['Sell'] as $goodID => $amount)
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