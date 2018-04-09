<?php

$type = trim($_POST['type']);

switch($type){
	case 'add':
		$sectorId = trim($_POST['sectorId']);
		$label = trim($_POST['label']);
		$player->addDestinationButton($sectorId, $label);
   break;

	case 'move':
		$sectorId = trim($_POST['sectorId']);
		$offsetTop = $_POST['offsetTop'];
		$offsetLeft = $_POST['offsetLeft'];
		$player->moveDestinationButton($sectorId, $offsetTop, $offsetLeft);
	break;

	case 'delete':
		$sectorId = trim($_POST['sectorId']);
		$player->deleteDestinationButton($sectorId);
	break;

	default:
		create_error("42 would be the right answer !!!");
	break;
}

$container = create_container('skeleton.php', 'course_plot.php');

forward($container);
