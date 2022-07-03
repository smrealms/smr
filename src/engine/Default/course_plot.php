<?php declare(strict_types=1);

use Smr\PlotGroup;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Plot A Course');

Menu::navigation($player);

$container = Page::create('course_plot_processing.php');
$template->assign('PlotCourseFormLink', $container->href());

$container = Page::create('course_plot_nearest_processing.php');
$template->assign('PlotNearestFormLink', $container->href());

if ($player->getShip()->hasJump()) {
	$container = Page::create('sector_jump_processing.php');
	$container['target_page'] = 'current_sector.php';
	$template->assign('JumpDriveFormLink', $container->href());
}

$container = Page::create('course_plot.php');
$template->assign('PlotToNearestHREF', $container->href());

$xtype = $session->getRequestVar('xtype', PlotGroup::Technology->value);
$template->assign('XType', PlotGroup::from($xtype));
$template->assign('AllXTypes', PlotGroup::cases());


// get saved destinations
$template->assign('StoredDestinations', $player->getStoredDestinations());
$container = Page::create('course_destination_button_processing.php');
$container['target_page'] = 'course_plot.php';
$template->assign('ManageDestination', $container->href());
