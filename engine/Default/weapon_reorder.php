<?

$smarty->assign('PageTopic','WEAPON REORDER');

if (isset($var['Up']) && is_numeric($var['Up']))
{
	$ship->moveWeaponUp($var['Up']);
}

if (isset($var['Down']) && is_numeric($var['Down']))
{
	$ship->moveWeaponDown($var['Down']);
}

if(isset($var['Form']))
{
	if(is_array($_REQUEST['weapon_reorder']))
		$ship->setWeaponLocations($_REQUEST['weapon_reorder']);
}
?>