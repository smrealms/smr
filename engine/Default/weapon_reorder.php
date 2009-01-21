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
?>