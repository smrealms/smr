<?php

$template->assign('PageTopic','Bounties');

require_once(get_file_loc('menu.inc'));
create_trader_menu();

$template->assign('AllClaims', array($player->getClaimableBounties('HQ'),
                                     $player->getClaimableBounties('UG')));

?>
