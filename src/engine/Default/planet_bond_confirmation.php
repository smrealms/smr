<?php declare(strict_types=1);

require('planet.inc.php');

$template->assign('BondDuration', format_time($planet->getBondTime()));
$template->assign('ReturnHREF', $planet->getFinancesHREF());
