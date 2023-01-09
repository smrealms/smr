<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class AllianceGovernance extends PlayerPage {

	public string $file = 'alliance_stat.php';

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$alliance_id = $this->allianceID;

		$alliance = Alliance::getAlliance($alliance_id, $player->getGameID());
		$account = $player->getAccount();
		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance_id);

		$role_id = $player->getAllianceRole($alliance->getAllianceID());

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$change_mod = $dbRecord->getBoolean('change_mod');
			$change_pass = $dbRecord->getBoolean('change_pass');
		} else {
			$change_mod = false;
			$change_pass = false;
		}
		$change_chat = $player->getAllianceID() == $alliance_id && $player->isAllianceLeader();

		$container = new AllianceGovernanceProcessor($alliance_id);
		$template->assign('FormHREF', $container->href());
		$template->assign('Alliance', $alliance);

		$template->assign('CanChangeDescription', $change_mod || $account->hasPermission(PERMISSION_EDIT_ALLIANCE_DESCRIPTION));
		$template->assign('CanChangePassword', $change_pass);
		$template->assign('CanChangeChatChannel', $change_chat);
		$template->assign('CanChangeMOTD', $change_mod);
		$template->assign('HidePassword', $alliance->getRecruitType() != Alliance::RECRUIT_PASSWORD);
	}

}
