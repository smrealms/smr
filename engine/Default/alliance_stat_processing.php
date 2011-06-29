<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
require_once(get_file_loc('smr_alliance.inc'));
if (isset($_REQUEST['password']))
	$password = trim($_REQUEST['password']);
if (isset($_REQUEST['description']))
	$description = trim($_REQUEST['description']);
if (isset($_REQUEST['mod']))
{
	if(preg_match('/"/',$_REQUEST['mod']))
		create_error('You cannot use a " in the image link!');
	$mod = trim($_REQUEST['mod']);
}
if (isset($_REQUEST['url']))
	$url = trim($_REQUEST['url']);
if (isset($password) && $password == '')
	create_error('You cannot set an empty password!');

if (isset($description) && $description == '')
	create_error('Please enter a description for your alliance.');

$alliance = new SMR_ALLIANCE($alliance_id, $player->getGameID());
if (isset($password))
	$alliance->setPassword($password);
if (isset($description))
	$alliance->setAllianceDescription($description);
if (isset($mod))
	$alliance->setMotD($mod);
if (isset($url))
	$alliance->setImageURL($url);

$alliance->update();
$container = create_container('skeleton.php', 'alliance_roster.php');
$container['alliance_id'] = $alliance_id;
forward($container);

?>