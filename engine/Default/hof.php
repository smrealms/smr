<?php
require_once(get_file_loc('HoF.inc'));

$o = $var['o'];
if(isset($_POST['o'])){
    $o = trim($_POST['o']);
}

$hof = HoF::getHofRanking($db,$o);

$ourRank = 0;

foreach($hof as $rank=>$obj){
    if($obj['accountId']==$player->getAccountID()){
        $ourRank = $rank+1;
        break;
    }
}

$offset = (isset($_POST['offset']))? trim($_POST['offset']):$ourRank;

$upperHof = $hof;
array_splice($upperHof, 10);
$template->assign('UpperHof', $upperHof);

$lowerHof = $hof;
array_slice($lowerHof, $offset, 10);
$template->assign('LowerHof', $lowerHof);

$container = create_container('skeleton.php', 'hof.php', $var);
$container['o']='1';
$loginDown = SmrSession::getNewHREF($container);

$container['o']='3';
$experienceDown = SmrSession::getNewHREF($container);

$container['o']='5';
$scoreDown = SmrSession::getNewHREF($container);

$container['o']='7';
$killsDown = SmrSession::getNewHREF($container);


$template->assign('OrderLinks', array('ExperienceHref'=> $experienceDown, 'LoginHref' => $loginDown, 'OperationHref' => $scoreDown, 'KillsHref' => $killsDown));

$template->assign('Form', SmrSession::getNewHREF($container));
$template->assign('O', $o);

?>