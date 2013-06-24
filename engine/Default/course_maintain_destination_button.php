<?php

function processDeleteButton($player, $db){
    $playerTargetSectorId = trim($_POST['playerTargetSectorId']);

    // perform some basic checks on both numbers
    if (empty($playerTargetSectorId) ){
        create_error('Which button do you want to move ?');
    }

    if (!is_numeric($playerTargetSectorId) ){
        create_error('Your kidding me, or ?');
    }

    // target stored ?
    $pd = null;
    foreach($player->getStoredDestinations() as &$sd){
        if($sd['id'] == $playerTargetSectorId){
            $query = "DELETE FROM player_stored_sector where player_stored_sector_id=".$db->escapeNumber($playerTargetSectorId);
            $db->query($query);
            unset($player->savedDestinations[$sd]);
            $player->savedDestinations = array_values($player->savedDestinations);
            break;
        }
    }
}

function processMoveButton($player, $db){
    $playerTargetSectorId = trim($_POST['playerTargetSectorId']);
    $offsetTop = $_POST['offsetTop'];
    $offsetLeft = $_POST['offsetLeft'];

    // perform some basic checks on both numbers
    if (empty($playerTargetSectorId) ){
        create_error('Which button do you want to move ?');
    }

    if (!is_numeric($playerTargetSectorId) ){
        create_error('Your kidding me, or ?');
    }

    if (empty($offsetTop) ){
        create_error('Dude, offsetTop not set; do you want to cheat ?');
    }

    if (empty($offsetLeft) ){
        create_error('Dude, offsetLeft not set; do you want to cheat ?');
    }

    if( !is_numeric($offsetLeft) || !is_numeric($offsetTop)){
        create_error('offset must be a number.');
    }
    $offsetTop = round($offsetTop);
    $offsetLeft = round($offsetLeft);

    if($offsetLeft < 0 || $offsetLeft > 800 || $offsetTop < 0 || $offsetTop > 800){
        create_error('offset must be between 0 and 800.');
    }

    // target stored ?
    $pd = null;
    foreach($player->getStoredDestinations()  as $sd){
        if($sd['id'] == $playerTargetSectorId){
            $pd = $sd;
            $sd['offsetTop']=$offsetTop;
            $sd['offsetLeft']=$offsetLeft;
        }
    }

    if(!$pd){
        create_error('Dude, you do not own that button. ');
    }

    $query = "UPDATE player_stored_sector SET offset_left=".$offsetLeft.", offset_top=".$offsetTop." WHERE player_stored_sector_id=".$playerTargetSectorId;
    $db->query($query);
}

function processAddDestination($player, $sector, $db){

    $sectorId = trim($_POST['sectorId']);
    $label = trim($_POST['label']);

    // perform some basic checks on both numbers
    if (empty($sectorId) )
        create_error('Where do you want to go today?');

    if (!is_numeric($sectorId))
        create_error('Please enter only numbers!');

    // get sector of throw exception in case not available in game
    try{
        $destSetor = $sector->getSector($player->getPlayerID(),$sectorId);
    }catch (Exception $e) {
        create_error("Sector is unknown or does not exist");
    }

    // sector already stored ?
    foreach($player->getStoredDestinations() as $sd){
        if($sd['sectorId'] == $sectorId){
            create_error('Sector already stored!');
        }
    }

    $query = "INSERT INTO player_stored_sector (player_id, sector_id, label, offset_top, offset_left) VALUES (".$db->escapeNumber($player->getPlayerID()).", ".$db->escapeNumber($sectorId).",".$db->escapeString($label,true).",1,1)";
    $db->query($query);
}



$type = trim($_POST['type']);

switch($type){
    case 'add':
        processAddDestination($player, $sector, $db);
   break;

    case 'move':
        processMoveButton($player,$db);
    break;

    case 'delete':
        processDeleteButton($player,$db);
    break;

    default:
        create_error("42 would be the right answer !!!");
    break;
}

$player->refreshCache();

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot.php';

forward($container);
?>