<?php
$template->assign('PageTopic','Search Trader');
$template->assign('TraderSearchHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'trader_search_result.php')));
?>