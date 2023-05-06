<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class CurrentPlayers extends PlayerPage {

	use ReusableTrait;

	public string $file = 'current_players.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$inactiveTime = Epoch::time() - TIME_BEFORE_INACTIVE;

		$template->assign('PageTopic', 'Current Players');
		$db = Database::getInstance();
		$db->write('DELETE FROM cpl_tag WHERE expires > 0 AND expires < :now', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);

		$dbResult = $db->read('SELECT count(*) count FROM active_session
					WHERE last_accessed >= :inactive_time AND
						game_id = :game_id', [
			'inactive_time' => $db->escapeNumber($inactiveTime),
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$count_active = $dbResult->record()->getInt('count');

		$dbResult = $db->read('SELECT * FROM player
				WHERE last_cpl_action >= :inactive_time
					AND game_id = :game_id
				ORDER BY experience DESC, player_name DESC', [
			'inactive_time' => $db->escapeNumber($inactiveTime),
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$count_moving = $dbResult->getNumRecords();

		// fix it if some1 is using the logoff button
		$count_active = max($count_active, $count_moving);

		// Get the summary text
		$summary = 'There ';
		if ($count_active !== 1) {
			$summary .= 'are ' . $count_active . ' players who have ';
		} else {
			$summary .= 'is 1 player who has ';
		}
		$summary .= 'accessed the server in the last ' . format_time(TIME_BEFORE_INACTIVE) . '.<br />';

		if ($count_moving === 0) {
			$summary .= 'No one was moving so your ship computer can\'t intercept any transmissions.';
		} else {
			if ($count_moving === $count_active) {
				$summary .= 'All ';
			} else {
				$summary .= 'A few ';
			}
			$summary .= 'of them were moving so your ship computer was able to intercept ' . pluralise($count_moving, 'transmission') . '.';
		}

		$summary .= '<br />The traders listed in <span class="italic">italics</span> are still ranked as Newbie or Beginner.';

		$template->assign('Summary', $summary);

		$allRows = [];
		foreach ($dbResult->records() as $dbRecord) {
			$row = [];

			$curr_player = Player::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
			$row['player'] = $curr_player;

			// How should we style the row for this player?
			$class = '';
			if ($player->equals($curr_player)) {
				$class .= 'bold';
			}
			if ($curr_player->hasNewbieStatus()) {
				$class .= ' newbie';
			}
			if ($class !== '') {
				$class = ' class="' . trim($class) . '"';
			}
			$row['tr_class'] = $class;

			// What should the player name be displayed as?
			$container = new SearchForTraderResult($curr_player->getPlayerID());
			$name = $curr_player->getLevelName() . ' ' . $curr_player->getDisplayName();
			$dbResult2 = $db->read('SELECT * FROM cpl_tag WHERE account_id = :account_id ORDER BY custom DESC', [
				'account_id' => $db->escapeNumber($curr_player->getAccountID()),
			]);
			foreach ($dbResult2->records() as $dbRecord2) {
				$customRank = $dbRecord2->getString('custom_rank');
				$tag = $dbRecord2->getString('tag');
				if (!empty($customRank)) {
					$name = $customRank . ' ' . $curr_player->getDisplayName();
				}
				if (!empty($tag)) {
					$name .= ' ' . $tag;
				}
			}
			$row['name_link'] = create_link($container, $name);

			$allRows[] = $row;
		}

		$template->assign('AllRows', $allRows);
	}

}
