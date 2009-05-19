<?

$template->assign('PageTopic','PLOT A COURSE');

$container=array();
$container['url'] = 'course_plot_processing.php';
$container['body'] = '';

$template->assign('PlotCourseFormLink',SmrSession::get_new_href($container));
if ($ship->hasJump())
{
	$container=create_container('sector_jump_processing.php','');
	$container['target_page'] = 'current_sector.php';
	$template->assign('JumpDriveFormLink',SmrSession::get_new_href($container));
}
?>