<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class CheatingShipCheck extends AccountPage {

	public string $file = 'admin/ship_check.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Ship Integrity Check');

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM ship_type_support_hardware, player, ship_has_hardware, hardware_type ' .
				   'WHERE ship_type_support_hardware.ship_type_id = player.ship_type_id AND ' .
						 'player.account_id = ship_has_hardware.account_id AND ' .
						 'player.game_id = ship_has_hardware.game_id AND ' .
						 'ship_type_support_hardware.hardware_type_id = ship_has_hardware.hardware_type_id AND ' .
						 'ship_has_hardware.hardware_type_id = hardware_type.hardware_type_id AND ' .
						 'amount > max_amount');

		$excessHardware = [];
		foreach ($dbResult->records() as $dbRecord) {
			$container = new CheatingShipCheckProcessor(
				accountID: $dbRecord->getInt('account_id'),
				hardwareTypeID: $dbRecord->getInt('hardware_type_id'),
				gameID: $dbRecord->getInt('game_id'),
				maxAmount: $dbRecord->getInt('max_amount')
			);

			$excessHardware[] = [
				'player' => htmlentities($dbRecord->getString('player_name')),
				'game_id' => $dbRecord->getInt('game_id'),
				'hardware' => $dbRecord->getString('hardware_name'),
				'amount' => $dbRecord->getInt('amount'),
				'max_amount' => $dbRecord->getInt('max_amount'),
				'fixHREF' => $container->href(),
			];
		}
		$template->assign('ExcessHardware', $excessHardware);
	}

}
