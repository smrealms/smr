<?php
$smarty->assign('PageTopic','Edit Dummys');

require_once(get_file_loc('DummyPlayer.class.inc'));
$smarty->assign('EditDummysLink',SmrSession::get_new_href(create_container('skeleton.php','edit_dummys.php')));

$dummyPlayer =& DummyPlayer::getCachedDummyPlayer($_REQUEST['dummy_name']);
$dummyShip =& $dummyPlayer->getShip();

if(isset($_REQUEST['save_dummy']))
{
	$dummyPlayer->setPlayerName($_REQUEST['dummy_name']);
	$dummyPlayer->setExperience($_REQUEST['level']);
	$dummyPlayer->setShipTypeID($_REQUEST['ship_id']);
}


$smarty->assign('DummyNames', DummyPlayer::getDummyPlayerNames());
$smarty->assign_by_ref('DummyPlayer',$dummyPlayer);
$smarty->assign_by_ref('DummyShip',$dummyShip);
$smarty->assign_by_ref('Levels',Globals::getLevelRequirements());

//TODO add game type id
$smarty->assign_by_ref('Ships',AbstractSmrShip::getAllBaseShips(0));

?>