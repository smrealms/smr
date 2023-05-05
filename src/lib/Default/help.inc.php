<?php declare(strict_types=1);

use Smr\Database;

function echo_nav(int $topic_id): void {
	// database object
	$db = Database::getInstance();

	// get current entry
	$dbResult = $db->read('SELECT * FROM manual WHERE topic_id = :topic_id', [
		'topic_id' => $db->escapeNumber($topic_id),
	]);
	if (!$dbResult->hasRecord()) {
		echo ('Invalid Topic!');
		return;
	}

	$dbRecord = $dbResult->record();
	$order_id = $dbRecord->getInt('order_id');
	$topic = stripslashes($dbRecord->getString('topic'));

	$parent_topic_id = $dbRecord->getInt('parent_topic_id');
	$dbResult = $db->read('SELECT * FROM manual WHERE topic_id = :topic_id', [
		'topic_id' => $db->escapeNumber($parent_topic_id),
	]);
	if ($dbResult->hasRecord()) {
		$dbRecord = $dbResult->record();
		$parent = [
			'id' => $dbRecord->getInt('topic_id'),
			'topic' => $dbRecord->getString('topic'),
			'parent_topic_id' => $dbRecord->getInt('parent_topic_id'),
			'order_id' => $dbRecord->getInt('order_id'),
		];
	}

	echo ('<table>');
	echo ('<tr>');

	// **************************
	// **  PREVIOUS
	// **************************
	$dbResult = $db->read('SELECT * FROM manual WHERE parent_topic_id = :parent_topic_id AND order_id = :order_id', [
		'parent_topic_id' => $db->escapeNumber($parent_topic_id),
		'order_id' => $db->escapeNumber($order_id - 1),
	]);
	echo ('<th width="32">');
	if ($dbResult->hasRecord()) {
		$dbRecord = $dbResult->record();
		$previous = [
			'id' => $dbRecord->getInt('topic_id'),
			'topic' => stripslashes($dbRecord->getString('topic')),
		];
	} elseif (isset($parent)) {
		$previous = $parent;
	}

	if (isset($previous)) {
		echo ('<a href="/manual.php?' . $previous['id'] . '"><img src="/images/help/previous.jpg" width="32" height="32" border="0"></a>');
	} else {
		echo ('<img src="/images/help/empty.jpg" width="32" height="32">');
	}
	echo ('</th>');

	// **************************
	// **  UP
	// **************************
	echo ('<th width="32">');
	if (isset($parent)) {
		$up = $parent;
		echo ('<a href="/manual.php?' . $up['id'] . '"><img src="/images/help/up.jpg" width="32" height="32" border="0"></a>');
	} else {
		echo ('<img src="/images/help/empty.jpg" width="32" height="32">');
	}
	echo ('</th>');

	// **************************
	// **  NEXT
	// **************************
	$dbResult = $db->read('SELECT * FROM manual WHERE parent_topic_id = :parent_topic_id AND order_id = 1', [
		'parent_topic_id' => $db->escapeNumber($topic_id),
	]);

	if (!$dbResult->hasRecord()) {
		$dbResult = $db->read('SELECT * FROM manual WHERE parent_topic_id = :parent_topic_id AND order_id = :order_id', [
			'parent_topic_id' => $db->escapeNumber($parent_topic_id),
			'order_id' => $db->escapeNumber($order_id + 1),
		]);
	}

	if (!$dbResult->hasRecord() && isset($parent)) {
		$dbResult = $db->read('SELECT * FROM manual WHERE parent_topic_id = :parent_topic_id AND order_id = :order_id', [
			'parent_topic_id' => $db->escapeNumber($parent['parent_topic_id']),
			'order_id' => $db->escapeNumber($parent['order_id'] + 1),
		]);
	}

	echo ('<th width="32">');
	if ($dbResult->hasRecord()) {
		$dbRecord = $dbResult->record();
		$next = [
			'id' => $dbRecord->getInt('topic_id'),
			'topic' => stripslashes($dbRecord->getString('topic')),
		];
		echo ('<a href="/manual.php?' . $next['id'] . '"><img src="/images/help/next.jpg" width="32" height="32" border="0"></a>');
	} else {
		echo ('<img src="/images/help/empty.jpg" width="32" height="32">');
	}
	echo ('</th>');

	echo ('<th width="100%" class="center" validn="middle" style="font-size:18pt;font-weight:bold;">' . get_numbering($topic_id) . $topic . '</th>');
	echo ('<th width="32"><a href="/manual_toc.php"><img src="/images/help/contents.jpg" width="32" height="32" border="0"></a></th>');

	echo ('</tr>');

	echo ('<tr>');
	echo ('<td colspan="5">');
	if (isset($previous) && $previous['id'] > 0) {
		echo ('<b>Previous:</b> <a href="/manual.php?' . $previous['id'] . '">' . get_numbering($previous['id']) . $previous['topic'] . '</a>&nbsp;&nbsp;&nbsp;');
	}
	if (isset($up) && $up['id'] > 0) {
		echo ('<b>Up:</b> <a href="/manual.php?' . $up['id'] . '">' . get_numbering($up['id']) . $up['topic'] . '</a>&nbsp;&nbsp;&nbsp;');
	}
	if (isset($next) && $next['id'] > 0) {
		echo ('<b>Next:</b> <a href="/manual.php?' . $next['id'] . '">' . get_numbering($next['id']) . $next['topic'] . '</a>');
	}
	echo ('</tr>');

	echo ('</table>');
}

function echo_content(int $topic_id): void {
	// database object
	$db = Database::getInstance();

	// get current entry
	$dbResult = $db->read('SELECT * FROM manual WHERE topic_id = :topic_id', [
		'topic_id' => $topic_id,
	]);
	if ($dbResult->hasRecord()) {
		$dbRecord = $dbResult->record();
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

function echo_subsection(int $topic_id): void {
	// database object
	$db = Database::getInstance();

	// check if there are subsections
	$dbResult = $db->read('SELECT 1 FROM manual WHERE parent_topic_id = :parent_topic_id ORDER BY order_id', [
		'parent_topic_id' => $db->escapeNumber($topic_id),
	]);
	if ($dbResult->hasRecord()) {
		echo ('<hr noshade width="75%" size="1" class="center"/>');
		echo ('<div id="help_menu">');
		echo ('<h2>Subsections:</h2>');

		echo_menu($topic_id);

		echo ('</div>');
	}
}

function echo_menu(int $topic_id): void {
	// database object
	$db = Database::getInstance();

	$dbResult = $db->read('SELECT * FROM manual WHERE parent_topic_id = :parent_topic_id ORDER BY order_id', [
		'parent_topic_id' => $db->escapeNumber($topic_id),
	]);
	if ($dbResult->hasRecord()) {
		echo ('<ul type="disc">');
		foreach ($dbResult->records() as $dbRecord) {
			$sub_topic_id = $dbRecord->getInt('topic_id');
			$sub_topic = stripslashes($dbRecord->getString('topic'));

			echo ('<li><a href="/manual.php?' . $sub_topic_id . '">' . get_numbering($sub_topic_id) . $sub_topic . '</a></li>');
			echo_menu($sub_topic_id);
		}
		echo ('</ul>');
	}
}

function get_numbering(int $topic_id): string {
	$db = Database::getInstance();

	$dbResult = $db->read('SELECT * FROM manual WHERE topic_id = :topic_id', [
		'topic_id' => $db->escapeNumber($topic_id),
	]);
	if (!$dbResult->hasRecord()) {
		return '';
	}

	$dbRecord = $dbResult->record();
	$up_topic_id = $dbRecord->getInt('parent_topic_id');
	$order_id = $dbRecord->getInt('order_id');

	return get_numbering($up_topic_id) . $order_id . '. ';
}
