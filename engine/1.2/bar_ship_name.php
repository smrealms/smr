<?php

$num_creds = $account->get_credits();
if ($num_creds == 0) {
	
	print_error("You don't have enough SMR Credits.  Donate money to SMR to gain SMR Credits!");
	return;
	
}
if (isset($_REQUEST["action"])) $action = $_REQUEST["action"];
if ($action == "Include HTML (2 SMR Credits)") $html = TRUE;
elseif (isset($var["html"])) $html = $var["html"];
if (isset($_REQUEST["ship_name"])) $name = $_REQUEST["ship_name"];
elseif (isset($var["ship_name"])) $name = $var["ship_name"];
$done = $var["done"];
$continue = $_REQUEST["continue"];
if (empty($html)) $continue = TRUE;
if ($action == "Paint a logo (3 SMR Credits)") {
	
	// check if we have an image
	if ($_FILES['photo']['error'] == UPLOAD_ERR_OK) {
	
		// get dimensions
		$size = getimagesize($_FILES['photo']['tmp_name']);
		// check if we really have a jpg
		if ($size[2] < 1 || $size[2] > 3) {
			
			print_error("Only gif, jpg or png-image allowed! s = $size[2]");
			return;
			
		}
	
		// check if width > 200
		if ($size[0] > 200) {
			
			print_error("Image is wider than 200 pixels!");
			return;
			
		}
	
		// check if height > 30
		if ($size[1] > 30) {
			
			print_error("Image is higher than 30 pixels!");
			return;
		
		}
		if (filesize($_FILES['photo']['tmp_name']) > 20560 && SmrSession::$old_account_id >= 100) {
			
			print_error("Image is bigger than 20k");
			return;
			
		}
		
		$orig_name = "<img style=\"padding: 3px 3px 3px 3px;\" src=\"".URL."/upload/" . SmrSession::$old_account_id . "logo\"><br>";
		$cred_cost = 3;
		$account->set_credits($num_creds - $cred_cost);
		$db->query("REPLACE INTO ship_has_name (game_id, account_id, ship_name) VALUES (" .
				"$player->game_id, $player->account_id, " . format_string($orig_name, FALSE) . ")");
		
		move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD . SmrSession::$old_account_id . "logo");
		print("<div align=center>Your logo was successfully painted!</div>");
		include(get_file_loc("bar_opening.php"));
		return;
		
	} else {
		
		print_error("Error while uploading");
		return;
		
	}
		
}

if ($action == "Include HTML (2 SMR Credits)" && !$done) {
	
	print("<div align=center>If you ship is found to use HTML inappropriatly you may be banned.");
	print("  Innappropriate HTML includes but is not limited to something that can either cause display errors or cause functionallity of the game to stop.  Also it is your responsibility to make sure ALL HTML tags that need to be closed are closed!<br>");
	print("Preview<br>" . stripslashes($name) . "<br></div>");
	print("Are you sure you want to continue?<br>");
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "bar_main.php";
	$container["script"] = "bar_ship_name.php";
	$container["process"] = "yes";
	$container["html"] = TRUE;
	$container["done"] = TRUE;
	$container["ship_name"] = stripslashes($name);
	print_form($container);
	print("Yes:<input type=radio name=continue value=TRUE><br>No:<input type=radio name=continue value=FALSE><br>");
	print_submit("Continue");
	print("</form>");
	
} elseif (isset($var["process"]) && $continue == "TRUE") {
	
	$orig_name = $name;
	if ($html) {
		
		$cred_cost = 2;
		$max_len = 128;
		$name = addslashes($name);
		//check for some bad html
		$bad = array('<form','<applet','<a ','<bgsound','<body','<meta','<dd','<dir','<dl','<!doctype','<dt','<embed','<frame','<head','<hr','<iframe','<ilayer','<img','<input','<isindex','<layer','<li','<link','<map','<menu','<nobr','<no','<object','<ol','<opt','<p','<script','<select','<sound','<td','<text','<t','<ul','<h','<br','</marquee><marquee','size','width','height','<div','width=','</marquee>%<marquee',"</marquee>?");
		foreach($bad as $check) {
			
			if (stristr($name, $check)) {
			
				$check .= "*>";
				if ($check != "<h*>" && $check != "</marquee>?*>") print_error(htmlentities($check, ENT_NOQUOTES) . " tag is not allowed in ship names.<br><small>If you believe the name is appropriate please contact an admin.</small>");
				elseif ($check == "</marquee>?*>") print_error("Sorry no text is allowed to follow a " . htmlentities("</marquee>", ENT_NOQUOTES) . " tag.");
				else print_error("Either you used the " . htmlentities($check, ENT_NOQUOTES) . " tag which is not allowed or the " . htmlentities("<html>", ENT_NOQUOTES) . " tag which is not needed.");
				return;
		
			}
			
		}
		list ($first, $second) = split ('</marquee>', $name);
		if ($second != "") {
			
			print_error("Sorry no text is allowed to follow a " . htmlentities("</marquee>", ENT_NOQUOTES) . " tag.");
			return;
			
		}
		list ($first, $second) = split ('<marquee>', $name);
		if ($first != "" && $second != "") {
			
			print_error("Sorry no text is allowed to come before a " . htmlentities("<marquee>", ENT_NOQUOTES) . " tag.");
			return;
			
		}
		//lets try to see if they closed all tages
		$first = explode ('<', $name);
		foreach ($first as $second) {
			
			if ($second == "") continue;
			// the / char will be 0 and evaluate to false unless we put something at the start
			$second = "." . $second;
			if (strpos($second, "/")) {
				
				$open -= 1;
				$close += 1;
				if ($open < 0) $ha = TRUE;
				
			}
			else {
				
				$real_open += 1;
				$open += 1;
				
			}
			
		}
		if ($open > 0) {
			
			print_error("You must close all HTML tags.  (i.e a &lt;font color=red&gt tag must have a &lt;/font&gt; tag somewhere after it).<br><small>If you think you received this message in error please contact an admin.");
			return;
			
		}
		if ($close > $real_open || $ha || $open < 0) {
			
			print_error("You can not close tags that do not exist!<br><small>This could be an attempt at hacking if this action is seen again it will be logged</small>");
			return;
			
		}
		
	} else {
		
		$max_len = 48;
		$cred_cost = 1;
		$name = addslashes(htmlentities($name, ENT_NOQUOTES));
		
	}
	
	//list of html tags that have an auto br
	$word = array('</marquee>');
	$done = FALSE;
	foreach ($word as $bad) {
		
		if (stristr($name, '$bad')) $done = TRUE;
		
	}
	if (!$done)	$orig_name .= "<br>";
	if (strlen($orig_name) > $max_len) {
		
		print_error("That won't fit on your ship!");
		return;
		
	}
	if ($num_creds < $cred_cost) {
	
		print_error("You don't have enough SMR Credits.  Donate money to SMR to gain SMR Credits!");
		return;
		
	}
	// disallow certain ascii chars
	for ($i = 0; $i < strlen($orig_name); $i++)
		if (ord($orig_name[$i]) < 32 || ord($orig_name[$i]) > 127 || in_array(ord($orig_name[$i]), array(37,39,59,92,63,42))) {
			
			print_error("The ship name contains invalid characters! " . chr(ord($orig_name[$i])));
			return;
			
		}
	$db->query("REPLACE INTO ship_has_name (game_id, account_id, ship_name) VALUES (" .
				"$player->game_id, $player->account_id, " . format_string($orig_name, FALSE) . ")");
	$account->set_credits($num_creds - $cred_cost);
	
	print("<div align=center>Thanks for your purchase! Your ship is ready!<br>");
	if ($html) print("If you ship is found to use HTML inappropriatly you may be banned.  If your ship does contain inappropriate HTML talk to an admin ASAP.");
	print("<br></div>");
	//offer another drink and such
	include(get_file_loc("bar_opening.php"));
	
} else {
		
	print_topic("Naming Your Ship");
	print("<div align=\"center\">");
	//get bar name
	$db->query("SELECT location_name FROM location_type NATURAL JOIN location WHERE game_id = $player->game_id AND sector_id = $player->sector_id AND location_type.location_type_id > 800 AND location_type.location_type_id < 900");
	
	//next welcome them
	if ($db->next_record()) print("<div align=center>So you want to name your ship?  Great!  " .
					"Anyone who knows anything will tell you " . $db->f("location_name") . " " .
					"is the place to get it done!<br><br>");
					
	print("So...what do you want to name it? (max 48 text chars) (max 30 height by 200 width and 20k for logos)<br>");
	//start form
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "bar_main.php";
	$container["script"] = "bar_ship_name.php";
	$container["process"] = "yes";
	print_form_parameter($container, "name=\"ship_naming\" enctype=\"multipart/form-data\"");
	print("<input type=\"text\" name=\"ship_name\" value=\"Enter Name Here\" id=\"InputFields\"><Br><br>");
	//print("Include HTML? (2 SMR Credits)<input type=checkbox name=html><br>");
	print_submit("Get It Painted! (1 SMR Credit)");
	print("<br><br>");
	print_submit("Include HTML (2 SMR Credits)");
	print("<br><br>");
	print("Image: <input type=\"file\" name=\"photo\" accept=\"image/jpeg\" id=\"InputFields\" style=\"width:40%;\">");
	print("<br>");
	print_submit("Paint a logo (3 SMR Credits)");
	print("</form></div>");

}

?>