<?php
$container = array();
$container['url'] = 'validate_processing.php';

if(isset($var['msg']))
	$template->assign('Message', $var['msg']);
$template->assign('ValidateFormHref', SmrSession::get_new_href($container));
$template->assign('FirstName', $account->first_name);

?>