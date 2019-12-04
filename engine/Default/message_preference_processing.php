<?php declare(strict_types=1);

if (isset($_POST['ignore_globals'])) {
	$player->setIgnoreGlobals($_POST['ignore_globals'] == 'Yes');
} elseif (isset($_POST['group_scouts'])) {
	$player->setGroupScoutMessages(strtoupper($_POST['group_scouts']));
}

$container = create_container('skeleton.php', 'message_view.php');
transfer('folder_id');
forward($container);
