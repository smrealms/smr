<?php

if ($player->getAlignment() >= 100) {

	create_error('You are not allowed to come in here!');
	return;

}

$template->assign('PageTopic','Underground Headquarters');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_ug_menue();

require_once(get_file_loc('gov.functions.inc'));
displayBountyList($PHP_OUTPUT,'UG',0);
displayBountyList($PHP_OUTPUT,'UG',$player->getAccountID());

if ($player->getAlignment() <= 99 && $player->getAlignment() >= -100) {

	$PHP_OUTPUT.=create_echo_form(create_container('government_processing.php', ''));
	$PHP_OUTPUT.=create_submit('Become a gang member');
	$PHP_OUTPUT.=('</form>');

}
?>