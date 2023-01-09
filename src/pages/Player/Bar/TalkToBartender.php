<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bar;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class TalkToBartender extends PlayerPage {

	public string $file = 'bar_talk_bartender.php';

	public function __construct(
		private readonly int $locationID,
		private ?string $message = null
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Talk to Bartender');
		Menu::bar($this->locationID);

		// We save the displayed message in session since it is randomized
		if ($this->message === null) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT message FROM bar_tender WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY rand() LIMIT 1');
			if ($dbResult->hasRecord()) {
				$message = 'I heard... ' . htmlentities(word_filter($dbResult->record()->getString('message'))) . '<br /><br />Got anything else to tell me?';
			} else {
				$message = 'I havent heard anything recently... got anything to tell me?';
			}
			$this->message = $message;
		}
		$template->assign('Message', bbifyMessage($this->message));

		$container = new self($this->locationID);
		$template->assign('ListenHREF', $container->href());

		$container = new TalkToBartenderProcessor($this->locationID);
		$template->assign('ProcessingHREF', $container->href());

		$container = new BarMain($this->locationID);
		$template->assign('BackHREF', $container->href());
	}

}
