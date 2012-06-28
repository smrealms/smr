<?php
$template->assign('PageTopic','Weapon Dealer');
$template->assignByRef('ThisLocation', SmrLocation::getLocation($var['LocationID']));
?>