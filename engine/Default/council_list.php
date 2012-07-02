<?php

require_once(get_file_loc('council.inc'));
require_once(get_file_loc('menu.inc'));

if (!isset($var['race_id'])) {
	SmrSession::updateVar('race_id',$player->getRaceID());
}
$raceID = $var['race_id'];

$template->assign('PageTopic','Ruling Council Of ' . Globals::getRaceName($raceID));
$template->assign('RaceID', $raceID);

create_council_menu($raceID);

// check for relations here
modifyRelations($raceID);

checkPacts($raceID);

?>