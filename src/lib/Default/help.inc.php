<?php declare(strict_types=1);

function echo_nav($topic_id) {
	// database object
	$db = Smr\Database::getInstance();

	// get current entry
	$dbResult = $db->read('SELECT * FROM manual WHERE topic_id = ' . $db->escapeNumber($topic_id));
	if ($dbResult->hasRecord()) {
		$dbRecord = $dbResult->record();
		$parent_topic_id = $dbRecord->getInt('parent_topic_id');
		$order_id = $dbRecord->getInt('order_id');
		$topic = stripslashes($dbRecord->getString('topic'));

		echo ('<table>');
		echo ('<tr>');

		// **************************
		// **  PREVIOUS
		// **************************
		$dbResult = $db->read('SELECT * FROM manual WHERE parent_topic_id = ' . $db->escapeNumber($parent_topic_id) . ' AND order_id = ' . $db->escapeNumber($order_id - 1));

		// no result?
		if (!$dbResult->hasRecord()) {
			$dbResult = $db->read('SELECT * FROM manual WHERE topic_id = ' . $db->escapeNumber($parent_topic_id));
		}

		echo ('<th width="32">');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$previous_topic_id = $dbRecord->getInt('topic_id');
			$previous_topic = stripslashes($dbRecord->getString('topic'));
			echo ('<a href="/manual.php?' . $previous_topic_id . '"><img src="/images/help/previous.jpg" width="32" height="32" border="0"></a>');
		} else {
			echo ('<img src="/images/help/empty.jpg" width="32" height="32">');
		}
		echo ('</th>');

		// **************************
		// **  UP
		// **************************
		$dbResult = $db->read('SELECT * FROM manual WHERE topic_id = ' . $db->escapeNumber($parent_topic_id));
		echo ('<th width="32">');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$up_topic_id = $dbRecord->getInt('topic_id');
			$up_topic = stripslashes($dbRecord->getString('topic'));
			echo ('<a href="/manual.php?' . $up_topic_id . '"><img src="/images/help/up.jpg" width="32" height="32" border="0"></a>');
		} else {
			echo ('<img src="/images/help/empty.jpg" width="32" height="32">');
		}
		echo ('</th>');

		// **************************
		// **  NEXT
		// **************************
		$dbResult = $db->read('SELECT * FROM manual WHERE parent_topic_id = ' . $db->escapeNumber($topic_id) . ' AND order_id = 1');

		if (!$dbResult->hasRecord()) {
			$dbResult = $db->read('SELECT * FROM manual WHERE parent_topic_id = ' . $db->escapeNumber($parent_topic_id) . ' AND order_id = ' . $db->escapeNumber($order_id + 1));
		}

		$seenParentIDs = array(0);
		$curr_parent_topic_id = $parent_topic_id;
		while (!$dbResult->hasRecord() && !in_array($curr_parent_topic_id, $seenParentIDs)) {
			$seenParentIDs[] = $curr_parent_topic_id;
			$dbResult2 = $db->read('SELECT * FROM manual WHERE topic_id = ' . $db->escapeNumber($parent_topic_id));
			$dbRecord2 = $dbResult2->record();
			$curr_order_id = $dbRecord2->getInt('order_id');
			$curr_parent_topic_id = $dbRecord2->getInt('parent_topic_id');

			$dbResult = $db->read('SELECT * FROM manual WHERE parent_topic_id = ' . $db->escapeNumber($parent_topic_id) . ' AND order_id = ' . $db->escapeNumber($curr_order_id + 1));
		}

		echo ('<th width="32">');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$next_topic_id = $dbRecord->getInt('topic_id');
			$next_topic = stripslashes($dbRecord->getString('topic'));
			echo ('<a href="/manual.php?' . $next_topic_id . '"><img src="/images/help/next.jpg" width="32" height="32" border="0"></a>');
		} else {
			echo ('<img src="/images/help/empty.jpg" width="32" height="32">');
		}
		echo ('</th>');

		echo ('<th width="100%" class="center" validn="middle" style="font-size:18pt;font-weight:bold;">' . get_numbering($topic_id) . $topic . '</th>');
		echo ('<th width="32"><a href="/manual_toc.php"><img src="/images/help/contents.jpg" width="32" height="32" border="0"></a></th>');

		echo ('</tr>');

		echo ('<tr>');
		echo ('<td colspan="5">');
		if (isset($previous_topic_id) && $previous_topic_id > 0)
			echo ('<b>Previous:</b> <a href="/manual.php?' . $previous_topic_id . '">' . get_numbering($previous_topic_id) . $previous_topic . '</a>&nbsp;&nbsp;&nbsp;');
		if (isset($up_topic_id) && $up_topic_id > 0)
			echo ('<b>Up:</b> <a href="/manual.php?' . $up_topic_id . '">' . get_numbering($up_topic_id) . $up_topic . '</a>&nbsp;&nbsp;&nbsp;');
		if (isset($next_topic_id) && $next_topic_id > 0)
			echo ('<b>Next:</b> <a href="/manual.php?' . $next_topic_id . '">' . get_numbering($next_topic_id) . $next_topic . '</a>');
		echo ('</tr>');

		echo ('</table>');
	} else
		echo ('Invalid Topic!');
}

function echo_content($topic_id) {
	// database object
	$db = Smr\Database::getInstance();

	// get current entry
	$dbResult = $db->read('SELECT * FROM manual WHERE topic_id = ' . $topic_id);
	if ($dbResult->hasRecord()) {
		$dbRecord = $dbResult->record();
		$parent_topic_id = $dbRecord->getInt('parent_topic_id');
		$order_id = $dbRecord->getInt('order_id');
		$topic = stripslashes($dbRecord->getString('topic'));
		$text = stripslashes($dbRecord->getString('text'));

		echo ('<div id="help_content">');
		echo ('<h1>' . get_numbering($topic_id) . $topic . '</h1>');
		echo ('<p>' . $text . '<p>');
		echo ('</div>');
	} else {
		echo ('Invalid Topic!');
	}
}

function echo_subsection($topic_id) {
	// database object
	$db = Smr\Database::getInstance();
	$return = '';
	// check if there are subsections
	$dbResult = $db->read('SELECT 1 FROM manual WHERE parent_topic_id = ' . $db->escapeNumber($topic_id) . ' ORDER BY order_id');
	if ($dbResult->hasRecord()) {
		echo ('<hr noshade width="75%" size="1" class="center"/>');
		echo ('<div id="help_menu">');
		echo ('<h2>Subsections:</h2>');

		echo_menu($topic_id);

		echo ('</div>');
	}
	return $return;
}

function echo_menu($topic_id) {
	$return = '';
	// database object
	$db = Smr\Database::getInstance();

	$dbResult = $db->read('SELECT * FROM manual WHERE parent_topic_id = ' . $db->escapeNumber($topic_id) . ' ORDER BY order_id');
	if ($dbResult->hasRecord()) {
		echo ('<ul type="disc">');
		foreach ($dbResult->records() as $dbRecord) {
			$sub_topic_id = $dbRecord->getInt('topic_id');
			$order_id = $dbRecord->getInt('order_id');
			$sub_topic = stripslashes($dbRecord->getString('topic'));

			echo ('<li><a href="/manual.php?' . $sub_topic_id . '">' . get_numbering($sub_topic_id) . $sub_topic . '</a></li>');
			echo_menu($sub_topic_id);
		}
		echo ('</ul>');
	}
	return $return;
}

function get_numbering($topic_id) {
	$db = Smr\Database::getInstance();

	$dbResult = $db->read('SELECT * FROM manual WHERE topic_id = ' . $db->escapeNumber($topic_id));
	if ($dbResult->hasRecord()) {
		$dbRecord = $dbResult->record();
		$up_topic_id = $dbRecord->getInt('parent_topic_id');
		$order_id = $dbRecord->getInt('order_id');

		return get_numbering($up_topic_id) . $order_id . '. ';
	}
}
