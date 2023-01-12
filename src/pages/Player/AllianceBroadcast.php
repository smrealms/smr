<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class AllianceBroadcast extends PlayerPage {

	use ReusableTrait;

	public string $file = 'alliance_broadcast.php';

	public function __construct(
		private readonly int $allianceID,
		private readonly ?string $preview = null
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$alliance = Alliance::getAlliance($this->allianceID, $player->getGameID());
		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		$container = new MessageSendProcessor(allianceID: $this->allianceID);
		$template->assign('MessageSendFormHref', $container->href());

		$template->assign('Receiver', 'Whole Alliance');
		if ($this->preview !== null) {
			$template->assign('Preview', $this->preview);
		}
	}

}
