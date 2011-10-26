<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menue();

//echo the dump cargo message or other message.
if (isset($var['errorMsg']))
	$template->assign('ErrorMsg',$var['errorMsg']);
if (isset($var['msg']))
	$template->assign('Msg',$var['msg']);

$template->assignByRef('ThisPlanet',$planet);

doTickerAssigns($template, $player, $db);

$template->assign('LaunchFormLink',SmrSession::get_new_href(create_container('planet_launch_processing.php', '')));
?>