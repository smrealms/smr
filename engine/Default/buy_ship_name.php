<?php declare(strict_types=1);

$template->assign('PageTopic', 'Naming Your Ship');

if (isset($var['Preview'])) {
	$template->assign('Preview', $var['Preview']);
	$template->assign('ContinueHref', SmrSession::getNewHREF(create_container('skeleton.php', 'buy_ship_name_processing.php', array('ShipName'=>$var['Preview']))));
} else {
	$template->assign('ShipNameFormHref', SmrSession::getNewHREF(create_container('skeleton.php', 'buy_ship_name_processing.php')));
}
