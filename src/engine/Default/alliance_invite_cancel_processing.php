<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

// Delete the alliance invitation
$var['invite']->delete();
Page::create('skeleton.php', 'alliance_invite_player.php')->go();
