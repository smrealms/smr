<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Template;

class AnonBank extends PlayerPage {

	public string $file = 'bank_anon.php';

	public function __construct(
		private readonly ?string $message = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$account = $player->getAccount();

		// is account validated?
		if (!$account->isValidated()) {
			create_error('You are not validated so you cannot use banks.');
		}

		$template->assign('PageTopic', 'Anonymous Account');
		Menu::bank();

		$container = new AnonBankProcessor();
		$template->assign('AccessHREF', $container->href());

		$template->assign('Message', $this->message ?? '');

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM anon_bank
					WHERE owner_id=' . $db->escapeNumber($player->getAccountID()) . '
					AND game_id=' . $db->escapeNumber($player->getGameID()));

		$ownedAnon = [];
		foreach ($dbResult->records() as $dbRecord) {
			$anon = [];
			$anon['anon_id'] = $dbRecord->getInt('anon_id');
			$anon['password'] = $dbRecord->getString('password');
			$anon['amount'] = $dbRecord->getInt('amount');

			$dbResult2 = $db->read('SELECT MAX(time) FROM anon_bank_transactions
						WHERE game_id=' . $db->escapeNumber($player->getGameID()) . '
						AND anon_id=' . $db->escapeNumber($dbRecord->getInt('anon_id')) . ' GROUP BY anon_id');
			if ($dbResult2->hasRecord()) {
				$anon['last_transaction'] = date($account->getDateTimeFormat(), $dbResult2->record()->getInt('MAX(time)'));
			} else {
				$anon['last_transaction'] = 'No transactions';
			}

			$container = new AnonBankDetail($anon['anon_id']);
			$anon['href'] = $container->href();

			$ownedAnon[] = $anon;
		}
		$template->assign('OwnedAnon', $ownedAnon);

		$container = new AnonBankCreate();
		$template->assign('CreateHREF', $container->href());
	}

}
