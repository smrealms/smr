<?php declare(strict_types=1);

// Delete the alliance invitation
$var['invite']->delete();
Page::create('skeleton.php', 'alliance_invite_player.php')->go();
