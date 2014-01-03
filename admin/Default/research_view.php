<?php

require_once(get_file_loc('Research.class.inc'));

if(isset($var['errorMsg'])) {
	$template->assign('ErrorMessage',$var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Message',$var['msg']);
}

if(isset($_REQUEST['gameId'])) {
    SmrSession::updateVar('gameId',$_REQUEST['gameId']);
}

if(isset($var['gameId'])){
    $game = SmrGame::getGame($_REQUEST['gameId']);
    $research = new Research($_REQUEST['gameId']);
    $gr = $research->getGameResearchAss();
    $container = create_container('skeleton.php', 'research_ship_view.php');
    $container['gameId'] = $gr['game_id'];
    $template->assign('Game',$game);
    $template->assign('ShipResearchHref', SmrSession::getNewHREF($container));

}else{
    $db->query('SELECT * FROM smr.game WHERE FROM_UNIXTIME(end_date) > NOW()');
    $games = array();
    while ($db->nextRecord()){
        $games[] = array('ID'=>$db->getField('game_id'), 'Name' => $db->getField('game_name'));
    }
    $template->assign('Games',$games);
    $template->assign('SelectGameForResearchHref', SmrSession::getNewHREF(create_container('skeleton.php','research_view.php')));
}


?>