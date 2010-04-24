<?php

function print_nav($topic_id) {

	// database object
	$db = new SmrMySqlDatabase();
	$db2 = new SmrMySqlDatabase();
	$db3 = new SmrMySqlDatabase();

	// get current entry
	$db->query("SELECT * FROM manual WHERE topic_id = $topic_id");
	if ($db->next_record()) {

		$parent_topic_id = $db->f("parent_topic_id");
		$order_id = $db->f("order_id");
		$topic = stripslashes($db->f("topic"));

		print("<table>");
		print("<tr>");

		// **************************
		// **  PREVIOUS
		// **************************
		$db2->query("SELECT * FROM manual WHERE parent_topic_id = $parent_topic_id AND order_id = $order_id - 1");

		// no result?
		if (!$db2->nf())
			$db2->query("SELECT * FROM manual WHERE topic_id = $parent_topic_id");

		print("<th width=\"32\">");
		if ($db2->next_record()) {

			$previous_topic_id = $db2->f("topic_id");
			$previous_topic = stripslashes($db2->f("topic"));
			print("<a href=\"".URL."/manual.php?$previous_topic_id\"><img src=\"URL/images/help/previous.jpg\" width=\"32\" height=\"32\" border=\"0\"></a>");

		} else
			print("<img src=\"".URL."/images/help/empty.jpg\" width=\"32\" height=\"32\">");
		print("</th>");

		// **************************
		// **  UP
		// **************************
		$db2->query("SELECT * FROM manual WHERE topic_id = $parent_topic_id");
		print("<th width=\"32\">");
		if ($db2->next_record()) {

			$up_topic_id = $db2->f("topic_id");
			$up_topic = stripslashes($db2->f("topic"));
			print("<a href=\"".URL."/manual.php?$up_topic_id\"><img src=\"".URL."/images/help/up.jpg\" width=\"32\" height=\"32\" border=\"0\"></a>");

		} else
			print("<img src=\"".URL."/images/help/empty.jpg\" width=\"32\" height=\"32\">");
		print("</th>");

		// **************************
		// **  NEXT
		// **************************
		$db2->query("SELECT * FROM manual WHERE parent_topic_id = $topic_id AND order_id = 1");

		if (!$db2->nf())
			$db2->query("SELECT * FROM manual WHERE parent_topic_id = $parent_topic_id AND order_id = $order_id + 1");

		$curr_parent_topic_id = $parent_topic_id;
		while (!$db2->nf() && $curr_parent_topic_id != 0) {

			$db3->query("SELECT * FROM manual WHERE topic_id = $curr_parent_topic_id");
			$db3->next_record();
			$curr_order_id = $db3->f("order_id");
			$curr_parent_topic_id = $db3->f("parent_topic_id");

			$db2->query("SELECT * FROM manual WHERE parent_topic_id = $curr_parent_topic_id AND order_id = $curr_order_id + 1");

		}

		print("<th width=\"32\">");
		if ($db2->next_record()) {

			$next_topic_id = $db2->f("topic_id");
			$next_topic = stripslashes($db2->f("topic"));
			print("<a href=\"".URL."/manual.php?$next_topic_id\"><img src=\"".URL."/images/help/next.jpg\" width=\"32\" height=\"32\" border=\"0\"></a>");

		} else
			print("<img src=\"".URL."/images/help/empty.jpg\" width=\"32\" height=\"32\">");
		print("</th>");

		print("<th width=\"100%\" align=\"center\" validn=\"middle\" style=\"font-size:18pt;font-weight:bold;\">" . get_numbering($topic_id) . "$topic</th>");
		print("<th width=\"32\"><a href=\"".URL."/manual_toc.php\"><img src=\"".URL."/images/help/contents.jpg\" width=\"32\" height=\"32\" border=\"0\"></a></th>");

		print("</tr>");

		print("<tr>");
		print("<td colspan=\"5\">");
		if ($previous_topic_id > 0)
			print("<b>Previous:</b> <a href=\"".URL."/manual.php?$previous_topic_id\">" . get_numbering($previous_topic_id) . "$previous_topic</a>&nbsp;&nbsp;&nbsp;");
		if ($up_topic_id > 0)
			print("<b>Up:</b> <a href=\"".URL."/manual.php?$up_topic_id\">" . get_numbering($up_topic_id) . "$up_topic</a>&nbsp;&nbsp;&nbsp;");
		if ($next_topic_id > 0)
			print("<b>Next:</b> <a href=\"".URL."/manual.php?$next_topic_id\">" . get_numbering($next_topic_id) . "$next_topic</a>");
		print("</tr>");

		print("</table>");

	} else
		print("Invalid Topic!");

}

function print_content($topic_id) {

	// database object
	$db = new SmrMySqlDatabase();

	// get current entry
	$db->query("SELECT * FROM manual WHERE topic_id = $topic_id");
	if ($db->next_record()) {

		$parent_topic_id = $db->f("parent_topic_id");
		$order_id = $db->f("order_id");
		$topic = stripslashes($db->f("topic"));
		$text = stripslashes($db->f("text"));

		print("<div id=\"help_content\">");
		print("<h1>" . get_numbering($topic_id) . "$topic</h1>");
		print("<p>$text<p>");
		print("</div>");

	} else
		print("Invalid Topic!");

}

function print_subsection($topic_id) {

	// database object
	$db = new SmrMySqlDatabase();

	// check if there are subsections
	$db->query("SELECT * FROM manual WHERE parent_topic_id = $topic_id ORDER BY order_id");
	if ($db->nf()) {

		print("<hr noshade width=\"75%\" size=\"1\" align=\"center\"/>");
		print("<div id=\"help_menu\">");
		print("<h2>Subsections:</h2>");

		print_menu($topic_id);

		print("</div>");
	}

}

function print_menu($topic_id) {


	// database object
	$db = new SmrMySqlDatabase();

	$db->query("SELECT * FROM manual WHERE parent_topic_id = $topic_id ORDER BY order_id");
	if ($db->nf()) {

		print("<ul type=\"disc\">");
		while($db->next_record()) {

			$sub_topic_id = $db->f("topic_id");
			$order_id = $db->f("order_id");
			$sub_topic = stripslashes($db->f("topic"));

			print("<li><a href=\"".URL."/manual.php?$sub_topic_id\">" . get_numbering($sub_topic_id) . "$sub_topic</a></li>");
			print_menu($sub_topic_id);

		}
		print("</ul>");

	}

}

function get_numbering($topic_id) {

	$db = new SmrMySqlDatabase();

	$db->query("SELECT * FROM manual WHERE topic_id = $topic_id");
	if ($db->next_record()) {

		$up_topic_id = $db->f("parent_topic_id");
		$order_id = $db->f("order_id");

		return get_numbering($up_topic_id) . $order_id . ". ";

	}

}

?>