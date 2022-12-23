<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\PlotGroup;
use Smr\Session;
use Smr\Template;

class PlotCourse extends PlayerPage {

	use ReusableTrait;

	public string $file = 'course_plot.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$session = Session::getInstance();

		$template->assign('PageTopic', 'Plot A Course');

		Menu::navigation($player);

		$container = new PlotCourseConventionalProcessor();
		$template->assign('PlotCourseFormLink', $container->href());

		$container = new PlotCourseNearestProcessor();
		$template->assign('PlotNearestFormLink', $container->href());

		if ($player->getShip()->hasJump()) {
			$container = new SectorJumpProcessor();
			$template->assign('JumpDriveFormLink', $container->href());
		}

		$container = new self();
		$template->assign('PlotToNearestHREF', $container->href());

		$xtype = $session->getRequestVar('xtype', PlotGroup::Technology->value);
		$template->assign('XType', PlotGroup::from($xtype));
		$template->assign('AllXTypes', PlotGroup::cases());

		// get saved destinations
		$template->assign('StoredDestinations', $player->getStoredDestinations());
		$container = new PlotCourseDestinationProcessor();
		$template->assign('ManageDestination', $container->href());
	}

}
