<?php

require('planet.inc');

$template->assign('BondDuration', format_time($planet->getBondTime()));
$template->assign('ReturnHREF', $planet->getFinancesHREF());
