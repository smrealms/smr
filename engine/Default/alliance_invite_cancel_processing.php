<?php declare(strict_types=1);

// Delete the alliance invitation
$var['invite']->delete();
forward(create_container('skeleton.php', 'alliance_invite_player.php'));
