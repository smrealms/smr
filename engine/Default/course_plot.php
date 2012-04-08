<?php

$template->assign('PageTopic','Plot A Course');

require_once(get_file_loc('menu.inc'));
create_nav_menu($template,$player);

$container=array();
$container['url'] = 'course_plot_processing.php';
$container['body'] = '';

$template->assign('PlotCourseFormLink',SmrSession::getNewHREF($container));
$container['url'] = 'course_plot_nearest_processing.php';
$template->assign('PlotNearestFormLink',SmrSession::getNewHREF($container));

if ($ship->hasJump()) {
	$container=create_container('sector_jump_processing.php','');
	$container['target_page'] = 'current_sector.php';
	$template->assign('JumpDriveFormLink',SmrSession::getNewHREF($container));
}
if(isset($_REQUEST['xtype']))
	SmrSession::updateVar('XType',$_REQUEST['xtype']);
else if(!isset($var['XType']))
	SmrSession::updateVar('XType','Technology');
$template->assign('XType',$var['XType']);
$template->assign('AllXTypes',array('Technology','Ships','Weapons','Locations','Goods'));
?>