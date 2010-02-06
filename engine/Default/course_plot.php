<?php

$template->assign('PageTopic','Plot A Course');

require_once(get_file_loc('menue.inc'));
create_nav_menue($template,$player);

$container=array();
$container['url'] = 'course_plot_processing.php';
$container['body'] = '';

$template->assign('PlotCourseFormLink',SmrSession::get_new_href($container));
$container['url'] = 'course_plot_nearest_processing.php';
$template->assign('PlotNearestFormLink',SmrSession::get_new_href($container));

if ($ship->hasJump())
{
	$container=create_container('sector_jump_processing.php','');
	$container['target_page'] = 'current_sector.php';
	$template->assign('JumpDriveFormLink',SmrSession::get_new_href($container));
}
if(isset($_REQUEST['xtype']))
		$template->assign('XType',$_REQUEST['xtype']);
$template->assign('AllXTypes',array('Technology','Ships','Weapons','Locations','Goods'));
?>