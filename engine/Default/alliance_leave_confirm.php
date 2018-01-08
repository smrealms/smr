<?php

$alliance =& $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$PHP_OUTPUT.= 'Do you really want to leave this alliance?<br /><br />';

$container = create_container('alliance_leave_processing.php');
$container['action'] = 'YES';

$PHP_OUTPUT.=create_button($container,'Yes!');
$container['action'] = 'NO';
$PHP_OUTPUT.= '&nbsp;&nbsp;&nbsp;';
$PHP_OUTPUT.=create_button($container,'No!');

?>
