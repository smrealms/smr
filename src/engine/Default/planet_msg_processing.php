<?php declare(strict_types=1);

$container = Page::create('skeleton.php', 'message_view.php');
$container['folder_id'] = MSG_PLANET;
$container->go();
