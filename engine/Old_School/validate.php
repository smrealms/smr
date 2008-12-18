<?
$container = array();
$container['url'] = 'validate_processing.php';

$smarty->assign('ValidationFormAction', 'loader.php');
$smarty->assign('ValidateFormSN', SmrSession::get_new_sn($container));
$smarty->assign('FirstName', $account->first_name);

?>