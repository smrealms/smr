<?php

$template->assign('PageTopic','IP Search');
$db->query('SELECT max(account_id) max_account_id FROM account');
$db->nextRecord();
$template->assign('MaxAccountID',$db->getInt('max_account_id'));

$template->assign('IpFormHref',SmrSession::getNewHREF(create_container('skeleton.php','ip_view_results.php')));
