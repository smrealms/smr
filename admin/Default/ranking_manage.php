<?php
    require_once(get_file_loc('Rankings.inc'));

    $template->assign('Processor', SmrSession::getNewHREF(create_container('skeleton.php', 'ranking_manage_process.php')));
    $template->assign('Rankings',Rankings::getRankings($db));
?>