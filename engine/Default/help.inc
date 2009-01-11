<?

function echo_nav($topic_id) {

	// database object
	$db = new SmrMySqlDatabase();
	$db2 = new SmrMySqlDatabase();
	$db3 = new SmrMySqlDatabase();

	// get current entry
	$db->query('SELECT * FROM manual WHERE topic_id = '.$topic_id);
	if ($db->next_record()) {

		$parent_topic_id = $db->f('parent_topic_id');
		$order_id = $db->f('order_id');
		$topic = stripslashes($db->f('topic'));

		echo ('<table>');
		echo ('<tr>');

		// **************************
		// **  PREVIOUS
		// **************************
		$db2->query('SELECT * FROM manual WHERE parent_topic_id = '.$parent_topic_id.' AND order_id = '.$order_id.' - 1');

		// no result?
		if (!$db2->nf())
			$db2->query('SELECT * FROM manual WHERE topic_id = '.$parent_topic_id);

		echo ('<th width="32">');
		if ($db2->next_record()) {

			$previous_topic_id = $db2->f('topic_id');
			$previous_topic = stripslashes($db2->f('topic'));
			echo ('<a href="'.URL.'/manual.php?'.$previous_topic_id.'"><img src="'.URL.'/images/help/previous.jpg" width="32" height="32" border="0"></a>');

		} else
			echo ('<img src="'.URL.'/images/help/empty.jpg" width="32" height="32">');
		echo ('</th>');

		// **************************
		// **  UP
		// **************************
		$db2->query('SELECT * FROM manual WHERE topic_id = '.$parent_topic_id);
		echo ('<th width="32">');
		if ($db2->next_record()) {

			$up_topic_id = $db2->f('topic_id');
			$up_topic = stripslashes($db2->f('topic'));
			echo ('<a href="'.URL.'/manual.php?'.$up_topic_id.'"><img src="'.URL.'/images/help/up.jpg" width="32" height="32" border="0"></a>');

		} else
			echo ('<img src="'.URL.'/images/help/empty.jpg" width="32" height="32">');
		echo ('</th>');

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

		echo ('<th width="32">');
		if ($db2->next_record()) {

			$next_topic_id = $db2->f('topic_id');
			$next_topic = stripslashes($db2->f('topic'));
			echo ('<a href="'.URL.'/manual.php?'.$next_topic_id.'"><img src="'.URL.'/images/help/next.jpg" width="32" height="32" border="0"></a>');

		} else
			echo ('<img src="'.URL.'/images/help/empty.jpg" width="32" height="32">');
		echo ('</th>');

		echo ('<th width="100%" align="center" validn="middle" style="font-size:18pt;font-weight:bold;">' . get_numbering($topic_id) . $topic.'</th>');
		echo ('<th width="32"><a href="'.URL.'/manual_toc.php"><img src="'.URL.'/images/help/contents.jpg" width="32" height="32" border="0"></a></th>');

		echo ('</tr>');

		echo ('<tr>');
		echo ('<td colspan="5">');
		if ($previous_topic_id > 0)
			echo ('<b>Previous:</b> <a href="'.URL.'/manual.php?'.$previous_topic_id.'">' . get_numbering($previous_topic_id) . $previous_topic.'</a>&nbsp;&nbsp;&nbsp;');
		if ($up_topic_id > 0)
			echo ('<b>Up:</b> <a href="'.URL.'/manual.php?'.$up_topic_id.'">' . get_numbering($up_topic_id) . $up_topic.'</a>&nbsp;&nbsp;&nbsp;');
		if ($next_topic_id > 0)
			echo ('<b>Next:</b> <a href="'.URL.'/manual.php?'.$next_topic_id.'">' . get_numbering($next_topic_id) . $next_topic.'</a>');
		echo ('</tr>');

		echo ('</table>');

	} else
		echo ('Invalid Topic!');

}

function echo_content($topic_id) {

	// database object
	$db = new SmrMySqlDatabase();

	// get current entry
	$db->query('SELECT * FROM manual WHERE topic_id = '.$topic_id);
	if ($db->next_record()) {

		$parent_topic_id = $db->f('parent_topic_id');
		$order_id = $db->f('order_id');
		$topic = stripslashes($db->f('topic'));
		$text = stripslashes($db->f('text'));

		echo ('<div id="help_content">');
		echo ('<h1>' . get_numbering($topic_id) . $topic.'</h1>');
		echo ('<p>'.$text.'<p>');
		echo ('</div>');

	} else
		echo ('Invalid Topic!');

}

function echo_subsection($topic_id) {

	// database object
	$db = new SmrMySqlDatabase();
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

function echo_menu($topic_id)
{
	$return='';
	// database object
	$db = new SmrMySqlDatabase();

	$db->query('SELECT * FROM manual WHERE parent_topic_id = '.$topic_id.' ORDER BY order_id');
	if ($db->nf()) {

		$return.=('<ul type="disc">');
		while($db->next_record()) {

			$sub_topic_id = $db->f('topic_id');
			$order_id = $db->f('order_id');
			$sub_topic = stripslashes($db->f('topic'));

			$return.=('<li><a href="'.URL.'/manual.php?'.$sub_topic_id.'">' . get_numbering($sub_topic_id) . $sub_topic.'</a></li>');
			$return.=create_menu($sub_topic_id);

		}
		$return.=('</ul>');

	}
	return $return;
}

function get_numbering($topic_id) {

	$db = new SmrMySqlDatabase();

	$db->query('SELECT * FROM manual WHERE topic_id = '.$topic_id);
	if ($db->next_record()) {

		$up_topic_id = $db->f('parent_topic_id');
		$order_id = $db->f('order_id');

		return get_numbering($up_topic_id) . $order_id . '. ';

	}

}

?>