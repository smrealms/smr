<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
require_once(get_file_loc('smr_alliance.inc'));
if (isset($_REQUEST['password']))
	$password = $_REQUEST['password'];
if (isset($_REQUEST['description']))
	$description = $_REQUEST['description'];
if (isset($_REQUEST['mod']))
	$mod = $_REQUEST['mod'];
if (isset($_REQUEST['url']))
	$url = $_REQUEST['url'];
if (isset($password) && $password == "")
	create_error("I advice you set a non empty password!");

if (isset($description) && $description == "")
	create_error("Please enter a description for your alliance");

$alliance = new SMR_ALLIANCE($alliance_id, $player->game_id);
if (isset($password))
	$alliance->password = $password;
if (isset($description))
	$alliance->description = $description;
if (isset($mod))
	$alliance->mod = $mod;
if (isset($url))
	$alliance->img_src = $url;

$alliance->update();
$container = create_container("skeleton.php", "alliance_roster.php");
$container['alliance_id'] = $alliance_id;
forward($container);

?>