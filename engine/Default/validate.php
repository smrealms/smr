<?
$container = array();
$container['url'] = 'validate_processing.php';

$template->assign('ValidationFormAction', 'loader.php');
$template->assign('ValidateFormSN', SmrSession::get_new_sn($container));
$template->assign('FirstName', $account->first_name);

?>