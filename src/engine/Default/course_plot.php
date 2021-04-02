<?php declare(strict_types=1);

$template->assign('PageTopic', 'Plot A Course');

Menu::navigation($template, $player);

$container = Page::create('course_plot_processing.php');

$template->assign('PlotCourseFormLink', $container->href());
$container['url'] = 'course_plot_nearest_processing.php';
$template->assign('PlotNearestFormLink', $container->href());

if ($ship->hasJump()) {
	$container = Page::create('sector_jump_processing.php');
	$container['target_page'] = 'current_sector.php';
	$template->assign('JumpDriveFormLink', $container->href());
}

$container = Page::create('skeleton.php', 'course_plot.php');
$template->assign('PlotToNearestHREF', $container->href());

$xtype = SmrSession::getRequestVar('xtype', 'Technology');
$template->assign('XType', $xtype);
$template->assign('AllXTypes', array('Technology', 'Ships', 'Weapons', 'Locations', 'Sell Goods', 'Buy Goods', 'Galaxies'));


// get saved destinations
$template->assign('StoredDestinations', $player->getStoredDestinations());
$container = Page::create('course_destination_button_processing.php');
$container['target_page'] = 'course_plot.php';
$template->assign('ManageDestination', $container->href());
