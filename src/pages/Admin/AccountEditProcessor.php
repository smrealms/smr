<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Epoch;
use Smr\Exceptions\UserError;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;
use SmrPlayer;

class AccountEditProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $editAccountID
	) {}

	public function build(SmrAccount $account): never {
		$db = Database::getInstance();

		$account_id = $this->editAccountID;
		$curr_account = SmrAccount::getAccount($account_id);

		// request
		$donation = Request::getInt('donation');
		$smr_credit = Request::has('smr_credit');
		$rewardCredits = Request::getInt('grant_credits');
		$choise = Request::get('choise', ''); // no radio button selected by default
		$reason_pre_select = Request::getInt('reason_pre_select');
		$reason_msg = Request::get('reason_msg');
		$veteran_status = Request::getBool('veteran_status');
		$logging_status = Request::getBool('logging_status');
		$except = Request::get('exception_add', ''); // missing if account already has an exception
		$points = Request::getInt('points');
		$names = Request::getArray('player_name', []); // missing when no games joined
		$delete = Request::getArray('delete', []); // missing when no games joined

		$actions = [];

		if (!empty($donation)) {
			// add entry to account donated table
			$db->insert('account_donated', [
				'account_id' => $db->escapeNumber($account_id),
				'time' => $db->escapeNumber(Epoch::time()),
				'amount' => $db->escapeNumber($donation),
			]);

			// add the credits to the players account - if requested
			if (!empty($smr_credit)) {
				$curr_account->increaseSmrCredits($donation * CREDITS_PER_DOLLAR);
			}

			$actions[] = 'added $' . $donation;
		}

		if (!empty($rewardCredits)) {
			$curr_account->increaseSmrRewardCredits($rewardCredits);
			$actions[] = 'added ' . $rewardCredits . ' reward credits';
		}

		if (Request::has('special_close')) {
			$specialClose = Request::get('special_close');
			// Make sure the special closing reason exists
			$dbResult = $db->read('SELECT reason_id FROM closing_reason WHERE reason=' . $db->escapeString($specialClose));
			if ($dbResult->hasRecord()) {
				$reasonID = $dbResult->record()->getInt('reason_id');
			} else {
				$reasonID = $db->insert('closing_reason', [
					'reason' => $db->escapeString($specialClose),
				]);
			}

			$closeByRequestNote = Request::get('close_by_request_note');
			if (empty($closeByRequestNote)) {
				$closeByRequestNote = $specialClose;
			}

			$curr_account->banAccount(0, $account, $reasonID, $closeByRequestNote);
			$actions[] = 'added ' . $specialClose . ' ban';
		}

		if ($choise == 'reopen') {
			//do we have points
			$curr_account->removePoints($points);
			$curr_account->unbanAccount($account);
			$actions[] = 'reopened account and removed ' . $points . ' points';
		} elseif ($points > 0) {
			if ($choise == 'individual') {
				$reason_id = $db->insert('closing_reason', [
					'reason' => $db->escapeString($reason_msg),
				]);
			} else {
				$reason_id = $reason_pre_select;
			}

			$suspicion = Request::get('suspicion');
			$bannedDays = $curr_account->addPoints($points, $account, $reason_id, $suspicion);
			$actions[] = 'added ' . $points . ' ban points';

			if ($bannedDays !== false) {
				if ($bannedDays > 0) {
					$expire_msg = 'for ' . $bannedDays . ' days';
				} else {
					$expire_msg = 'indefinitely';
				}
				$actions[] = 'closed ' . $expire_msg;
			}
		}

		if (Request::has('mailban')) {
			$mailban = Request::get('mailban');
			if ($mailban == 'remove') {
				$curr_account->setMailBanned(Epoch::time());
				$actions[] = 'removed mailban';
			} elseif ($mailban == 'add_days') {
				$days = Request::getInt('mailban_days');
				$curr_account->increaseMailBanned($days * 86400);
				$actions[] = 'mail banned for ' . $days . ' days';
			}
		}

		if ($veteran_status != $curr_account->isVeteranForced()) {
			$db->write('UPDATE account SET veteran = ' . $db->escapeBoolean($veteran_status) . ' WHERE account_id = ' . $db->escapeNumber($account_id));
			$actions[] = 'set the veteran status to ' . $db->escapeBoolean($veteran_status);
		}

		if ($logging_status != $curr_account->isLoggingEnabled()) {
			$curr_account->setLoggingEnabled($logging_status);
			$actions[] = 'set the logging status to ' . $logging_status;
		}

		if ($except != '') {
			$db->insert('account_exceptions', [
				'account_id' => $db->escapeNumber($account_id),
				'reason' => $db->escapeString($except),
			]);
			$actions[] = 'added the exception ' . $except;
		}

		foreach ($names as $game_id => $new_name) {
			if (empty($new_name)) {
				continue;
			}
			$editPlayer = SmrPlayer::getPlayer($account_id, $game_id);

			try {
				$editPlayer->changePlayerName($new_name);
			} catch (UserError $err) {
				$actions[] = 'have NOT changed player name to ' . htmlentities($new_name) . ' ( ' . $err->getMessage() . ')';
				continue;
			}
			$editPlayer->update();

			$actions[] = 'changed player name to ' . $editPlayer->getDisplayName();

			//insert news message
			$news = 'Please be advised that player ' . $editPlayer->getPlayerID() . ' has had their name changed to ' . $editPlayer->getBBLink();

			$db->insert('news', [
				'time' => $db->escapeNumber(Epoch::time()),
				'news_message' => $db->escapeString($news),
				'game_id' => $db->escapeNumber($game_id),
				'type' => $db->escapeString('admin'),
				'killer_id' => $db->escapeNumber($account_id),
			]);
		}

		if (!empty($delete)) {
			foreach ($delete as $game_id => $value) {
				if ($value == 'TRUE') {
					// Check for bank transactions into the alliance account
					$dbResult = $db->read('SELECT 1 FROM alliance_bank_transactions WHERE payee_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id) . ' LIMIT 1');
					if ($dbResult->hasRecord()) {
						// Can't delete
						$actions[] = 'player has made alliance transaction';
						continue;
					}

					$sql = 'account_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id);

					// Check anon accounts for transactions
					$dbResult = $db->read('SELECT 1 FROM anon_bank_transactions WHERE ' . $sql . ' LIMIT 1');
					if ($dbResult->hasRecord()) {
						// Can't delete
						$actions[] = 'player has made anonymous transaction';
						continue;
					}

					$db->write('DELETE FROM alliance_thread
								WHERE sender_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
					$db->write('DELETE FROM bounty WHERE ' . $sql);
					$db->write('DELETE FROM galactic_post_applications WHERE ' . $sql);
					$db->write('DELETE FROM galactic_post_article
								WHERE writer_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
					$db->write('DELETE FROM galactic_post_writer WHERE ' . $sql);
					$db->write('DELETE FROM message WHERE ' . $sql);
					$db->write('DELETE FROM message_notify
								WHERE (from_id=' . $db->escapeNumber($account_id) . ' OR to_id=' . $db->escapeNumber($account_id) . ') AND game_id=' . $db->escapeNumber($game_id));
					$db->write('UPDATE planet SET owner_id=0,planet_name=\'\',password=\'\',shields=0,drones=0,credits=0,bonds=0
								WHERE owner_id=' . $db->escapeNumber($account_id) . ' AND game_id=' . $db->escapeNumber($game_id));
					$db->write('DELETE FROM player_attacks_planet WHERE ' . $sql);
					$db->write('DELETE FROM player_attacks_port WHERE ' . $sql);
					$db->write('DELETE FROM player_has_alliance_role WHERE ' . $sql);
					$db->write('DELETE FROM player_has_drinks WHERE ' . $sql);
					$db->write('DELETE FROM player_has_relation WHERE ' . $sql);
					$db->write('DELETE FROM player_has_ticker WHERE ' . $sql);
					$db->write('DELETE FROM player_has_ticket WHERE ' . $sql);
					$db->write('DELETE FROM player_has_unread_messages WHERE ' . $sql);
					$db->write('DELETE FROM player_plotted_course WHERE ' . $sql);
					$db->write('DELETE FROM player_read_thread WHERE ' . $sql);
					$db->write('DELETE FROM player_visited_port WHERE ' . $sql);
					$db->write('DELETE FROM player_visited_sector WHERE ' . $sql);
					$db->write('DELETE FROM player_votes_pact WHERE ' . $sql);
					$db->write('DELETE FROM player_votes_relation WHERE ' . $sql);
					$db->write('DELETE FROM ship_has_cargo WHERE ' . $sql);
					$db->write('DELETE FROM ship_has_hardware WHERE ' . $sql);
					$db->write('DELETE FROM ship_has_illusion WHERE ' . $sql);
					$db->write('DELETE FROM ship_has_weapon WHERE ' . $sql);
					$db->write('DELETE FROM ship_is_cloaked WHERE ' . $sql);
					$db->write('DELETE FROM player WHERE ' . $sql);

					$db->write('UPDATE active_session SET game_id=0 WHERE ' . $sql . ' LIMIT 1');

					$actions[] = 'deleted player from game ' . $game_id;
				}
			}

		}

		//get his login name
		$msg = 'You ' . implode(' and ', $actions) . ' for the account of ' . $curr_account->getLogin() . '.';
		$container = new AccountEditSearch(message: $msg);

		// Update the selected account in case it has been changed
		$curr_account->update();
		$container->go();
	}

}
