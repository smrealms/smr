<?
if($var['toggle']=='WeaponHiding')
{
	$player->setDisplayWeapons(!$player->isDisplayWeapons());
}
else if($var['toggle']=='AJAX')
{
	$player->setUseAJAX(!$player->isUseAJAX());
}
if(!USING_AJAX)
{
	$container = array();
	$container['url'] = 'skeleton.php';
	if ($player->isLandedOnPlanet()) $container['body'] = 'planet_main.php';
	else $container['body'] = 'current_sector.php';
	forward($container);
}
?>