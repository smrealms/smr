<?php
require_once(get_file_loc('Rankings.inc'));

$action = trim($_REQUEST['action']);

switch($action){

    case "down":
        $rankingId = trim($_REQUEST['rankingId']);
        if(!is_numeric($rankingId)){
            create_error("I was looking for a numeric value");
        }

        Rankings::moveRanking($db, $rankingId, "down");
        break;

    case "up":
        $rankingId = trim($_REQUEST['rankingId']);
        if(!is_numeric($rankingId)){
            create_error("I was looking for a numeric value");
        }

        Rankings::moveRanking($db, $rankingId, "up");
    break;
    case "add":
        $label = trim($_REQUEST['label']);
        $experience = trim($_REQUEST['experience']) ?: 0;
        $kills = trim($_REQUEST['kills']) ?:0 ;
        $operation = trim($_REQUEST['operation'])?:0;
        $utility = trim($_REQUEST['utility']) ?:0;

        if(!$label){
            create_error("All fields must be filled");
        }

        if(!is_numeric($experience) || !is_numeric($kills) || !is_numeric($operation) || !is_numeric($utility)){
            create_error("numeric fields required for anything but the label");
        }

        Rankings::addRanking($db,$account,$label, $experience, $kills, $operation, $utility);
    break;

    case "delete":
        $rankingId = trim($_REQUEST['rankingId']);
        if(!is_numeric($rankingId)){
            create_error("I was looking for a numeric value");
        }

        Rankings::deleteRanking($db, $rankingId);
    break;

}

forward(create_container('skeleton.php', 'ranking_manage.php'));
?>