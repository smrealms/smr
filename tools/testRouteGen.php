<?php
$string = 'Fuck 123453124';
var_dump($string);
	$string = preg_replace('/FUCK/i',':)',$string);
	var_dump($string);

/*
class X{}

$arr = array();

for($i=0;$i<100000;$i++)
	$arr[] = new X();

var_dump(number_format(memory_get_usage()));
/*
require_once('../htdocs/config.inc');
require_once(LIB . 'Default/Globals.class.inc');
require_once(get_file_loc('RouteGenerator.class.inc'));
require_once(get_file_loc('SmrGalaxy.class.inc'));

$gameID = 2;

$galaxies =& SmrGalaxy::getGameGalaxies($gameID);
$allSectors = array();
foreach($galaxies as &$galaxy)
{
	$allSectors = $allSectors + $galaxy->getSectors();
} unset($galaxy);

$maxNumberOfPorts = 2;
$goods = array(true,true,true,true,false,false,false,false,false,false,false,false,false);
$races = array(true,true,true,true,false,false,false,false,false,false,false,false,false,false);

$distances =& Plotter::calculatePortToPortDistances($allSectors,10,0,1440);
//$distances =& Plotter::calculatePortToPortDistances($allSectors,10,0,15);
//var_dump($distances);

$routesForPort=-1;
$numberOfRoutes=5;

if ($maxNumberOfPorts == 1)
	$allRoutes = RouteGenerator::generateOneWayRoutes($allSectors, $distances, $goods, $races, $routesForPort);
else
	$allRoutes = RouteGenerator::generateMultiPortRoutes($maxNumberOfPorts, $allSectors, $goods, $races, $distances, $routesForPort, $numberOfRoutes);

//var_dump($allRoutes);

foreach($allRoutes as $routeType)
{
	$c = 0;
	foreach($routeType as $routeMulti)
	{
		$c += (count($routeMulti));
//		foreach($routeMulti as $multi => $route)
//		{
//			echo $multi . ":  ".$route->getRouteString();
//		}
//		echo "\r\n";
//		echo "\r\n";
	}
	var_dump($c);
}
/**/
?>