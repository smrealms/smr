<?php

require(ENGINE.'Default/alliance_option.php');

if ($player->isDraftLeader()) {
	$container['body'] = 'alliance_pick.php';
	$links[] = array(
		'link' => create_link($container, 'Pick Members'),
		'text' => 'Draft players into your alliance.',
	);
}

$template->assign('Links', $links);

?>
