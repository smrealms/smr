<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$account = $session->getAccount();

		$template->assign('PageTopic', 'Voting');

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM voting ORDER BY end DESC');
		if ($dbResult->hasRecord()) {
			$votedFor = [];
			$dbResult2 = $db->read('SELECT * FROM voting_results WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
			foreach ($dbResult2->records() as $dbRecord2) {
				$votedFor[$dbRecord2->getInt('vote_id')] = $dbRecord2->getInt('option_id');
			}

			$voting = [];
			foreach ($dbResult->records() as $dbRecord) {
				$voteID = $dbRecord->getInt('vote_id');
				$voting[$voteID]['ID'] = $voteID;
				$container = Page::create('vote_processing.php');
				$container['forward_to'] = 'vote.php';
				$container['vote_id'] = $voteID;
				$voting[$voteID]['HREF'] = $container->href();
				$voting[$voteID]['Question'] = $dbRecord->getString('question');
				if ($dbRecord->getInt('end') > Epoch::time()) {
					$voting[$voteID]['TimeRemaining'] = format_time($dbRecord->getInt('end') - Epoch::time(), true);
				} else {
					$voting[$voteID]['EndDate'] = date($account->getDateFormat(), $dbRecord->getInt('end'));
				}

				$voting[$voteID]['Options'] = [];
				$dbResult2 = $db->read('SELECT option_id,text,count(account_id) FROM voting_options LEFT OUTER JOIN voting_results USING(vote_id,option_id) WHERE vote_id = ' . $db->escapeNumber($dbRecord->getInt('vote_id')) . ' GROUP BY option_id');
				foreach ($dbResult2->records() as $dbRecord2) {
					$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['ID'] = $dbRecord2->getInt('option_id');
					$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['Text'] = $dbRecord2->getString('text');
					$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['Chosen'] = isset($votedFor[$dbRecord->getInt('vote_id')]) && $votedFor[$voteID] == $dbRecord2->getInt('option_id');
					$voting[$voteID]['Options'][$dbRecord2->getInt('option_id')]['Votes'] = $dbRecord2->getInt('count(account_id)');
				}
			}
			$template->assign('Voting', $voting);
		}
