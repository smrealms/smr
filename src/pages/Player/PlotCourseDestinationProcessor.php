<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class PlotCourseDestinationProcessor extends PlayerPageProcessor {

	public function build(AbstractSmrPlayer $player): never {
		$type = Request::get('type');
		$sectorId = Request::getInt('sectorId');

		switch ($type) {
			case 'add':
				$label = Request::get('label');
				$player->addDestinationButton($sectorId, $label);
				break;

			case 'move':
				// These are submitted as floats by ui.draggable.position JS, but
				// we (and the browser) only accept integer positions.
				$offsetTop = Request::getInt('offsetTop');
				$offsetLeft = Request::getInt('offsetLeft');
				$player->moveDestinationButton($sectorId, $offsetTop, $offsetLeft);
				break;

			case 'delete':
				$player->deleteDestinationButton($sectorId);
				break;

			default:
				create_error('42 would be the right answer !!!');
		}

		$container = new PlotCourse();
		$container->go();
	}

}
