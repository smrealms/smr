<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$type = Smr\Request::get('type');
$sectorId = Smr\Request::getInt('sectorId');

switch ($type) {
	case 'add':
		$label = trim(Smr\Request::get('label'));
		$player->addDestinationButton($sectorId, $label);
	break;

	case 'move':
		// These are submitted as floats by ui.draggable.position JS, but
		// we (and the browser) only accept integer positions.
		$offsetTop = Smr\Request::getInt('offsetTop');
		$offsetLeft = Smr\Request::getInt('offsetLeft');
		$player->moveDestinationButton($sectorId, $offsetTop, $offsetLeft);
	break;

	case 'delete':
		$player->deleteDestinationButton($sectorId);
	break;

	default:
		create_error('42 would be the right answer !!!');
	break;
}

$container = Page::create('skeleton.php', 'course_plot.php');

$container->go();
