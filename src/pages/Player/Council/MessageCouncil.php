<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Race;
use Smr\Template;

class MessageCouncil extends PlayerPage {

	use ReusableTrait;

	public string $file = 'council_send_message.php';

	public function __construct(
		private readonly int $raceID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$raceName = Race::getName($this->raceID);
		$template->assign('RaceName', $raceName);

		$template->assign('PageTopic', 'Send message to Ruling Council of the ' . $raceName);

		Menu::messages();

		$container = new MessageCouncilProcessor($this->raceID);
		$template->assign('SendHREF', $container->href());
	}

}
