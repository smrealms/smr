<?
require_once(get_file_loc('SmrForce.class.inc'));
$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

if($forces->getCDs() == 0 && $forces->getMines() == 0 && $forces->getSDs() == 1) {
	$days = 2;
}
else {
	$days = ceil(($forces->getCDs() + $forces->getSDs() + $forces->getMines()) / 10);
}
if ($days > 5) $days = 5;
$forces->setExpire(TIME + ($days * 86400));
$forces->update();

forward(create_container('skeleton.php', 'current_sector.php'));

?>
