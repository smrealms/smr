<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}
$alliance_id = $var['alliance_id'];
require_once(get_file_loc('SmrAlliance.class.inc'));
if (isset($_REQUEST['password'])) {
	$password = trim($_REQUEST['password']);
}
if (isset($_REQUEST['description'])) {
	$description = trim($_REQUEST['description']);
}
if (isset($_REQUEST['irc'])) {
	$irc = trim($_REQUEST['irc']);
}
if (isset($_REQUEST['mod'])) {
	$mod = trim($_REQUEST['mod']);
}
if (isset($_REQUEST['url'])) {
	$url = trim($_REQUEST['url']);
}

if(preg_match('/"/',$url)) {
	create_error('You cannot use a " in the image link!');
}

if (isset($password) && $password == '') {
	create_error('You cannot set an empty password!');
}

if (isset($description) && $description == '') {
	create_error('Please enter a description for your alliance.');
}

$alliance =& SmrAlliance::getAlliance($alliance_id, $player->getGameID());
if (isset($password)) {
	$alliance->setPassword($password);
}
if (isset($description)) {
	$alliance->setAllianceDescription($description);
}
if (isset($irc)) {
	$alliance->setIrcChannel($irc);
}
if (isset($mod)) {
	$alliance->setMotD($mod);
}
if (isset($url)) {
	$alliance->setImageURL($url);
}

$alliance->update();
$container = create_container('skeleton.php', 'alliance_roster.php');
$container['alliance_id'] = $alliance_id;
forward($container);

?>