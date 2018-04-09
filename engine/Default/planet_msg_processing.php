<?php

$container = create_container('skeleton.php', 'message_view.php');
$container['folder_id'] = MSG_PLANET;
forward($container);
