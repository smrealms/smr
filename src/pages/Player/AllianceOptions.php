<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Game;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class AllianceOptions extends PlayerPage {

	use ReusableTrait;

	public function build(Player $player, Template $template): void {
		$alliance = $player->getAlliance();
		$template->pageTopic = $alliance->getAllianceDisplayName(false, true);
		Menu::alliance($alliance->getAllianceID());

		// Create an array of links with descriptions
		$links = [];

		$isDraftGame = $player->getGame()->isGameType(Game::GAME_TYPE_DRAFT);

		if ($isDraftGame && $player->isDraftLeader()) {
			// Draft leaders get to pick members
			$page = new AllianceDraftMember();
			$links[] = [
				'link' => create_link($page, 'Draft Members'),
				'text' => 'Choose members to join your alliance.',
			];
		}

		if (!$isDraftGame && !$player->getAccount()->isNHL()) {
			// Players can choose to leave their alliance (except in Draft games)
			$page = new AllianceLeaveConfirm();
			$links[] = [
				'link' => create_link($page, 'Leave Alliance'),
				'text' => 'Leave the alliance. Alliance leaders must hand over leadership before leaving.',
			];
		}

		$page = new AllianceShareMapsProcessor();
		$links[] = [
			'link' => create_link($page, 'Share Maps'),
			'text' => 'Share your knowledge of the universe with your alliance mates.',
		];

		$role_id = $player->getAllianceRole($alliance->getAllianceID());

		$db = Database::getInstance();
		$dbResult = $db->select('alliance_has_roles', [
			...$alliance->SQLID,
			'role_id' => $role_id,
		]);
		$dbRecord = $dbResult->record();

		if ($dbRecord->getBoolean('change_pass') && !$alliance->isNHA()) {
			$page = new AllianceInvitePlayer();
			$links[] = [
				'link' => create_link($page, 'Invite Player'),
				'text' => 'Invite a player to the alliance.',
			];
		}
		if ($dbRecord->getBoolean('remove_member') && !$alliance->isNHA()) {
			$page = new AllianceRemoveMember();
			$links[] = [
				'link' => create_link($page, 'Remove Member'),
				'text' => 'Remove a trader from alliance roster.',
			];
		}
		if ($player->isAllianceLeader() && !$alliance->isNHA()) {
			$page = new AllianceLeadership();
			$links[] = [
				'link' => create_link($page, 'Handover Leadership'),
				'text' => 'Hand over leadership of the alliance to an alliance mate.',
			];
		}
		if (($dbRecord->getBoolean('change_pass') || $dbRecord->getBoolean('change_mod')) && !$alliance->isNHA()) {
			$page = new AllianceGovernance($alliance->getAllianceID());
			$links[] = [
				'link' => create_link($page, 'Govern Alliance'),
				'text' => 'Change the password, description or message of the day for the alliance.',
			];
		}
		if ($dbRecord->getBoolean('change_roles')) {
			$page = new AllianceRoles();
			$links[] = [
				'link' => create_link($page, 'Define Alliance Roles'),
				'text' => 'Each member in your alliance can fit into a specific role, a task. Here you can define the roles that you can assign to them.',
			];
		}
		if ($dbRecord->getBoolean('exempt_with')) {
			$page = new AllianceExemptAuthorize();
			$links[] = [
				'link' => create_link($page, 'Exempt Bank Transactions'),
				'text' => 'Here you can set certain alliance account transactions as exempt. This makes them not count against, or for, the player making the transaction in the bank report.',
			];
		}
		if ($dbRecord->getBoolean('treaty_entry')) {
			$page = new AllianceTreaties();
			$links[] = [
				'link' => create_link($page, 'Negotiate Treaties'),
				'text' => 'Negotitate treaties with other alliances.',
			];
		}
		if ($dbRecord->getBoolean('op_leader')) {
			$page = new AllianceSetOp();
			$links[] = [
				'link' => create_link($page, 'Schedule Operation'),
				'text' => 'Schedule and manage the next alliance operation and designate an alliance flagship.',
			];
		}
		if ($dbRecord->getBoolean('manage_npcs')) {
			$page = new AllianceManageNpcs();
			$links[] = [
				'link' => create_link($page, 'Manage NPCs'),
				'text' => 'Make changes to hired NPC traders.',
			];
		}

		$template->pageRenderer = fn() => AllianceOptionsRenderer::render($links);
	}

}
