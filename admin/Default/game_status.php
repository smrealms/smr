<?php

$db->query('SELECT * FROM game_disable');
if (!$db->getNumRows()) {

	$template->assign('PageTopic','Close Game');

	$container = array();
	$container['url'] = 'game_status_processing.php';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('If you wish to close the game please enter a reason for the closure.<br /><br />');
	$PHP_OUTPUT.=('<input spellcheck="true" type="text" name="close_reason" maxlength="255" size="100" id="InputFields"><br /><br />');
	$PHP_OUTPUT.=create_submit('Close');
	$PHP_OUTPUT.=('</form>');

} else {

	$template->assign('PageTopic','Open Game');

	$container = array();
	$container['url'] = 'game_status_processing.php';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('Do you want to reopen Space Merchant Realms?<br /><br />');
	$PHP_OUTPUT.=create_submit('Open');
	$PHP_OUTPUT.=('</form>');

}
