<?php

function print_error($message) {

	print("<h1>ERROR</h1>");
	print("<p><b><big>$message</big></b></p>");
	print("<br><br><br>");
	print("<p><small>If the error was caused by something you entered, press back and try again.</small></p>");
	print("<p><small>If it was a DB Error, press back and try again, or logoff and log back on.</small></p>");
	print("<p><small>If the error was unrecognizable, please notify the administrators.</small></p>");
}

function print_menue($menue) {

	echo '<div class="bar1"><div><span class="nowrap">';
	foreach ($menue as $number => $entry) {
		if ($number > 0) echo '</span> | <span class="nowrap">';
		echo $entry;
	}
	echo '</span></div></div><br />';
}

function print_topic($topic) {
	echo '<h1>' . strtoupper($topic) . '</h1><br />';
}

function print_table() {
 	echo '<table class="standard" cellspacing="0">';
}

function print_form($container) {
	print_form_name($container, "FORM");
}

function print_form_name($container, $name) {
	print_form_parameter($container, "name=\"$name\"");
}

function print_form_parameter($container, $param) {
	global $session;
	print("<form $param method=\"POST\" action=\"loader2.php\">");
	print("<input type=\"hidden\" name=\"sn\" value=\"" . SmrSession::get_new_sn($container) . "\">");
}

function print_header_link($container, $text) {
	global $session;
	echo '<a class="header" href="loader2.php?sn=' . SmrSession::get_new_sn($container) . '">' . $text . '</a>';
}

function print_link($container, $text) {
	global $session;
	echo '<a href="loader2.php?sn=' . SmrSession::get_new_sn($container) . '">' . $text . '</a>';
}

function print_button($container,$text) {
	global $session;
	echo '<div class="buttonA"><a class="buttonA"href="loader2.php?sn=' . SmrSession::get_new_sn($container). '">&nbsp;' . $text . '&nbsp;</a></div>';
}

function create_form($container,$actions) {
	global $session;
	static $form_id = 0;
	$form = array();
	$form['form'] = '<form class="standard" id="FORM' . $form_id . '" method="POST" action="loader2.php?sn=' . SmrSession::get_new_sn($container) . '">';

	if(!is_array($actions)) {
		$form['submit'] =  '<input class="submit" type="submit" name="action" value="' . $actions . '">';
	}
	else {
		$form['submit'] = array();
		foreach($actions as $action) {
		$form['submit'][$action[0]] =  '<input class="submit" type="submit" name="action" value="' . $action[1] . '">';
		}
	}

	++$form_id;
	return $form;

}

function print_submit($value) {
	print("<input type=\"submit\" name=\"action\" value=\"$value\" id=\"InputFields\">");
}

function print_submit_style($value, $style) {
	print("<input type=\"submit\" name=\"action\" value=\"$value\" id=\"InputFields\" style=\"$style\">");
}

function get_file_loc($file_name, $exists = 1) {

	global $session, $includes, $g_id;
	static $stored_dir;
	if(isset($stored_dir)) {
		$game_dir = $stored_dir;
	}
	else {
		if ((isset($session) && SmrSession::$game_id > 0) || $g_id > 0) {
			if ($g_id > 0) $game_id = $g_id;
			else $g_id = SmrSession::$game_id;
			$includes->query("SELECT game_type FROM game WHERE game_id = $g_id LIMIT 1	");
			$includes->next_record();
			$stored_dir = $includes->f("game_type") . "/";
			$game_dir = $stored_dir;
		} else $game_dir = "1.2/";
	}

	if (file_exists(ENGINE . $game_dir . $file_name)) return ENGINE . $game_dir . $file_name;
	elseif (file_exists(ENGINE . "1.2/" . $file_name)) return ENGINE . "1.2/" . $file_name;
	elseif (file_exists(ENGINE . "Default/" . $file_name)) return ENGINE . "Default/" . $file_name;
	elseif (file_exists(LIB . $game_dir . $file_name)) return LIB . $game_dir . $file_name;
	elseif (file_exists(LIB . "1.2/" . $file_name)) return LIB . "1.2/" . $file_name;
	elseif (file_exists(LIB . "Default/" . $file_name)) return LIB . "Default/" . $file_name;
	elseif (file_exists(ADMIN . $game_dir . $file_name)) return ADMIN . $game_dir . $file_name;
	elseif (file_exists(ADMIN . "1.2/" . $file_name)) return ADMIN . "1.2/" . $file_name;
	elseif (file_exists(ADMIN . "Default/" . $file_name)) return ADMIN . "Default/" . $file_name;
	elseif ($exists == 1) return ENGINE . "empty.php";
	else return $file_name;

}

function create_error($message) {

	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "error.php";
	$container["message"] = "$message";
	forward($container);
	
	/*
	global $player;
	if (empty($message) || $message = "") $message = "File not found";
	$container=array();
	$container['url'] = 'skeleton.php';
	if ($player->land_on_planet == "TRUE") $container['body'] = 'planet_main.php';
	else $container['body'] = 'current_sector.php';
	$errorMsg = "<span class=\"red bold\">ERROR:</span> " . $message . "!";
	$container['errorMsg'] = "$errorMsg";
	forward($container);*/

}

function forward($new_container) {

	global $session, $time_start,$sn,$container,$var;

	// add start time of that script to next page
	$new_container['time'] = $time_start;
	// this sn identifies our container later

	SmrSession::addLink($new_container,$sn);

	SmrSession::update();
	$var = $new_container;
	$container = $new_container;

	do_voodoo();

}

function transfer($what) {

	global $var, $container;

	// transfer this value to next container
	$container[$what] = $var[$what];

}

function create_container($file, $body) {

	$container = array();
	$container["url"] = $file;
	$container["body"] = $body;

	return $container;

}

function format_string($db_str, $transfer_html) {

	if ($transfer_html)
		return "'" . addslashes(htmlentities($db_str, ENT_NOQUOTES)) . "'";
	else
		return "'" . addslashes($db_str) . "'";

}

function create_link($container, $text) {
	global $session;
	return '<a href="loader2.php?sn=' . SmrSession::get_new_sn($container) . '">' . $text . '</a>';
}

function get_colored_text($value, $text) {

	if($value < 0) {
		$r_component='ff';
		if($value < -300) $value=-300;
		$temp = ceil((300 + $value)* 0.85);
		$g_component = dechex($temp);
		if($temp < 16) $g_component = '0' . $g_component;
	}
	else if($value > 0) {
		$g_component='ff';
		if($value > 300) $value=300;
		$temp = ceil((300 - $value) * 0.85);
		$r_component = dechex($temp);
		if($temp < 16) $r_component = '0' . $r_component;
	}
	else {
		$r_component = 'ff';
		$g_component = 'ff';
	}

	return '<span style="color:#' . $r_component . $g_component . '00">' . $text . '</span>';

}

function makeBold($value) {

	if ($value)
		return " style=\"font-weight:bold;\"";
	else
		return "";

}

function get_form_value($name) {

	global $_POST;

	if (!empty($_POST[$name]))
		return $_POST[$name];
	else
		return 0;

}

function different_level($rank1, $rank2, $forced_vet1, $forced_vet2) {
// remove newbie gals
	return false;
	
	// we are newbie, he vet
	if ($rank1 < FLEDGLING && $rank2 > BEGINNER && $forced_vet1 == "FALSE")
		return true;

	// we are vet, he newbie
	if ($rank1 > BEGINNER && $rank2 < FLEDGLING && $forced_vet2 == "FALSE")
		return true;

	// we are a forces vet, he newbie
	if ($forced_vet1 == "TRUE" && $rank2 < FLEDGLING && $forced_vet2 == "FALSE")
		return true;

	// we newbie, he forced vet
	if ($rank1 < FLEDGLING && $forced_vet2 == "TRUE" && $forced_vet1 == "FALSE")
		return true;

	return false;

}

function word_filter($string) {
	
	static $words;
	
	if(!is_array($words)) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT word_value, word_replacement FROM word_filter');
		$words = array();
		while($db->next_record()) {
			$row = $db->fetch_row();
			$words[] = array('word_value' => '/'.str_replace('/','\/',$row['word_value']).'/i','word_replacement'=> $row['word_replacement']);
		}
	}
	
	foreach ($words as $word) {
		$string = preg_replace($word['word_value'],$word['word_replacement'],$string);
	}

	return $string;
	
}

?>
