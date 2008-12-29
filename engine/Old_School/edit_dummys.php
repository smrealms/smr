<?php
$smarty->assign('PageTopic','Edit Dummys');

require_once(get_file_loc('DummyPlayer.class.inc'));
$smarty->assign('EditDummysLink',SmrSession::get_new_href(create_container('skeleton.php','edit_dummys.php')));

$smarty->assign('DummyNames', DummyPlayer::getDummyNames());

?>
