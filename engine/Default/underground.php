<?php

if ($player->getAlignment() >= ALIGNMENT_GOOD) {
	create_error('You are not allowed to come in here!');
}

if(!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}
$location =& SmrLocation::getLocation($var['LocationID']);
if(!$location->isUG()) {
	create_error('There is no underground here.');
}

$template->assign('PageTopic','Underground Headquarters');

require_once(get_file_loc('menu.inc'));
create_ug_menu();

$PHP_OUTPUT .= '<p>The location appears to be abandoned, until a group of heavily-armed figures advance from the shadows.</p>';

require_once(get_file_loc('gov.functions.inc'));
displayBountyList($PHP_OUTPUT,'UG',0);
displayBountyList($PHP_OUTPUT,'UG',$player->getAccountID());

if ($player->getAlignment() < ALIGNMENT_GOOD && $player->getAlignment() >= ALIGNMENT_EVIL) {
	$container = create_container('government_processing.php');
	transfer('LocationID');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Become a gang member');
	$PHP_OUTPUT.=('</form>');
}
?>
