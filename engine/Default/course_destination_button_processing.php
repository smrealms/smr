<?php

$type = trim($_POST['type']);

switch($type){
    case 'add':
        $sectorId = trim($_POST['sectorId']);
        $label = trim($_POST['label']);
        $player->addDestinationButton($sectorId, $label, $db);
   break;

    case 'move':
        $playerTargetSectorId = trim($_POST['playerTargetSectorId']);
        $offsetTop = $_POST['offsetTop'];
        $offsetLeft = $_POST['offsetLeft'];
        $player->moveDestinationButton($playerTargetSectorId, $offsetTop, $offsetLeft, $db);
    break;

    case 'delete':
        $playerTargetSectorId = trim($_POST['playerTargetSectorId']);
        $player->deleteDestinationButton($playerTargetSectorId,$db);
    break;

    default:
        create_error("42 would be the right answer !!!");
    break;
}

$player->refreshCache();

$container = create_container('skeleton.php', 'course_plot.php');

forward($container);
?>