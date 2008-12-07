<?

function echo_nav($topic_id) {

	global $URL;

	// database object
	$db = new SMR_DB();
	$db2 = new SMR_DB();
	$db3 = new SMR_DB();

	// get current entry
	$db->query('SELECT * FROM manual WHERE topic_id = '.$topic_id);
	if ($db->next_record()) {

		$parent_topic_id = $db->f('parent_topic_id');
		$order_id = $db->f('order_id');
		$topic = stripslashes($db->f('topic'));

		$PHP_OUTPUT.=('<table>');
		$PHP_OUTPUT.=('<tr>');

		// **************************
		// **  PREVIOUS
		// **************************
		$db2->query('SELECT * FROM manual WHERE parent_topic_id = '.$parent_topic_id.' AND order_id = '.$order_id.' - 1');

		// no result?
		if (!$db2->nf())
			$db2->query('SELECT * FROM manual WHERE topic_id = '.$parent_topic_id);

		$PHP_OUTPUT.=('<th width="32">');
		if ($db2->next_record()) {

			$previous_topic_id = $db2->f('topic_id');
			$previous_topic = stripslashes($db2->f('topic'));
			$PHP_OUTPUT.=('<a href="'.$URL.'/manual.php?'.$previous_topic_id.'"><img src="'.$URL.'/images/help/previous.jpg" width="32" height="32" border="0"></a>');

		} else
			$PHP_OUTPUT.=('<img src="'.$URL.'/images/help/empty.jpg" width="32" height="32">');
		$PHP_OUTPUT.=('</th>');

		// **************************
		// **  UP
		// **************************
		$db2->query('SELECT * FROM manual WHERE topic_id = '.$parent_topic_id);
		$PHP_OUTPUT.=('<th width="32">');
		if ($db2->next_record()) {

			$up_topic_id = $db2->f('topic_id');
			$up_topic = stripslashes($db2->f('topic'));
			$PHP_OUTPUT.=('<a href="'.$URL.'/manual.php?'.$up_topic_id.'"><img src="'.$URL.'/images/help/up.jpg" width="32" height="32" border="0"></a>');

		} else
			$PHP_OUTPUT.=('<img src="'.$URL.'/images/help/empty.jpg" width="32" height="32">');
		$PHP_OUTPUT.=('</th>');

		// **************************
		// **  NEXT
		// **************************
		$db2->query('SELECT * FROM manual WHERE parent_topic_id = '.$topic_id.' AND order_id = 1');

		if (!$db2->nf())
			$db2->query('SELECT * FROM manual WHERE parent_topic_id = '.$parent_topic_id.' AND order_id = '.$order_id.' + 1');

		$curr_parent_topic_id = $parent_topic_id;
		while (!$db2->nf() && $curr_parent_topic_id != 0) {

			$db3->query('SELECT * FROM manual WHERE topic_id = '.$curr_parent_topic_id);
			$db3->next_record();
			$curr_order_id = $db3->f('order_id');
			$curr_parent_topic_id = $db3->f('parent_topic_id');

			$db2->query('SELECT * FROM manual WHERE parent_topic_id = '.$curr_parent_topic_id.' AND order_id = '.$curr_order_id.' + 1');

		}

		$PHP_OUTPUT.=('<th width="32">');
		if ($db2->next_record()) {

			$next_topic_id = $db2->f('topic_id');
			$next_topic = stripslashes($db2->f('topic'));
			$PHP_OUTPUT.=('<a href="'.$URL.'/manual.php?'.$next_topic_id.'"><img src="'.$URL.'/images/help/next.jpg" width="32" height="32" border="0"></a>');

		} else
			$PHP_OUTPUT.=('<img src="'.$URL.'/images/help/empty.jpg" width="32" height="32">');
		$PHP_OUTPUT.=('</th>');

		$PHP_OUTPUT.=('<th width="100%" align="center" validn="middle" style="font-size:18pt;font-weight:bold;">' . get_numbering($topic_id) . $topic.'</th>');
		$PHP_OUTPUT.=('<th width="32"><a href="'.$URL.'/manual_toc.php"><img src="'.$URL.'/images/help/contents.jpg" width="32" height="32" border="0"></a></th>');

		$PHP_OUTPUT.=('</tr>');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td colspan="5">');
		if ($previous_topic_id > 0)
			$PHP_OUTPUT.=('<b>Previous:</b> <a href="'.$URL.'/manual.php?'.$previous_topic_id.'">' . get_numbering($previous_topic_id) . $previous_topic.'</a>&nbsp;&nbsp;&nbsp;');
		if ($up_topic_id > 0)
			$PHP_OUTPUT.=('<b>Up:</b> <a href="'.$URL.'/manual.php?'.$up_topic_id.'">' . get_numbering($up_topic_id) . $up_topic.'</a>&nbsp;&nbsp;&nbsp;');
		if ($next_topic_id > 0)
			$PHP_OUTPUT.=('<b>Next:</b> <a href="'.$URL.'/manual.php?'.$next_topic_id.'">' . get_numbering($next_topic_id) . $next_topic.'</a>');
		$PHP_OUTPUT.=('</tr>');

		$PHP_OUTPUT.=('</table>');

	} else
		$PHP_OUTPUT.=('Invalid Topic!');

}

function echo_content($topic_id) {

	// database object
	$db = new SMR_DB();

	// get current entry
	$db->query('SELECT * FROM manual WHERE topic_id = '.$topic_id);
	if ($db->next_record()) {

		$parent_topic_id = $db->f('parent_topic_id');
		$order_id = $db->f('order_id');
		$topic = stripslashes($db->f('topic'));
		$text = stripslashes($db->f('text'));

		$PHP_OUTPUT.=('<div id="help_content">');
		$PHP_OUTPUT.=('<h1>' . get_numbering($topic_id) . $topic.'</h1>');
		$PHP_OUTPUT.=('<p>'.$text.'<p>');
		$PHP_OUTPUT.=('</div>');

	} else
		$PHP_OUTPUT.=('Invalid Topic!');

}

function echo_subsection($topic_id) {

	// database object
	$db = new SMR_DB();
	$return='';
	// check if there are subsections
	$db->query('SELECT * FROM manual WHERE parent_topic_id = '.$topic_id.' ORDER BY order_id');
	if ($db->nf()) {

		$return.=('<hr noshade width="75%" size="1" align="center"/>');
		$return.=('<div id="help_menu">');
		$return.=('<h2>Subsections:</h2>');

		$return.=create_menu($topic_id);

		$return.=('</div>');
	}
	return $return;
}

function echo_menu($topic_id) {

	global $URL;
	$return='';
	// database object
	$db = new SMR_DB();

	$db->query('SELECT * FROM manual WHERE parent_topic_id = '.$topic_id.' ORDER BY order_id');
	if ($db->nf()) {

		$return.=('<ul type="disc">');
		while($db->next_record()) {

			$sub_topic_id = $db->f('topic_id');
			$order_id = $db->f('order_id');
			$sub_topic = stripslashes($db->f('topic'));

			$return.=('<li><a href="'.$URL.'/manual.php?'.$sub_topic_id.'">' . get_numbering($sub_topic_id) . $sub_topic.'</a></li>');
			$return.=create_menu($sub_topic_id);

		}
		$return.=('</ul>');

	}
	return $return;
}

function get_numbering($topic_id) {

	$db = new SMR_DB();

	$db->query('SELECT * FROM manual WHERE topic_id = '.$topic_id);
	if ($db->next_record()) {

		$up_topic_id = $db->f('parent_topic_id');
		$order_id = $db->f('order_id');

		return get_numbering($up_topic_id) . $order_id . '. ';

	}

}

?>