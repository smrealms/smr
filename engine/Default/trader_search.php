<?php
$template->assign('PageTopic','Search Trader');
$template->assign('TraderSearchHREF', SmrSession::get_new_href(create_container('skeleton.php', 'trader_search_result.php')));
?>