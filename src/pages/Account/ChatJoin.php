<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Session;
use Smr\Template;

class ChatJoin extends AccountPage {

	use ReusableTrait;

	public string $file = 'chat_rules.php';

	public function build(Account $account, Template $template): void {
		$session = Session::getInstance();
		$player = $session->hasGame() ? $session->getPlayer() : null;

		$template->assign('PageTopic', 'Space Merchant Realms Chat');

		$autoChannels = '#SMR';
		$nick = 'SMR-';
		if (isset($player) && $player->hasAlliance()) {
			$allianceChan = $player->getAlliance()->getIrcChannel();
			if ($allianceChan) {
				$autoChannels .= ',' . $allianceChan;
			}
			$nick .= $player->getPlayerName();
		} else {
			$nick .= $account->getHofName();
		}

		$ircURL = 'http://widget.mibbit.com/?settings=5f6a385735f22a3138c5cc6059dab2f4&server=irc.theairlock.net&autoconnect=true&channel=' . urlencode($autoChannels) . '&nick=' . urlencode(str_replace(' ', '_', $nick));
		$template->assign('IrcURL', $ircURL);
	}

}
