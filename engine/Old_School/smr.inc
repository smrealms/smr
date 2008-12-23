<?
require_once(LIB . 'page/SmrSession.class.inc');

function has_beta_privilege($priv = '', $acc = 0)
{
	global $ADMINS, $PRIVILEGES, $ACCOUNT_ID,$BETA_CONSTANTS,$USE_BETA_CONSTANTS;
	//PAGE
	return false;
	if($USE_BETA_CONSTANTS !== true)
		return false;
	if ($acc == 0) $acc = $ACCOUNT_ID;
	switch($priv)
	{
		case 'Create Universe':
			if(isset($BETA_CONSTANTS['Anyone Create Games']) && $BETA_CONSTANTS['Anyone Create Games']===true)
			return true;
	}
	
	if(isset($BETA_CONSTANTS[$priv]))
	{
		if ($BETA_CONSTANTS[$priv] === true) return true;
		if (is_array($BETA_CONSTANTS[$priv]) && in_array($acc, $BETA_CONSTANTS[$priv])) return true;
	}
	if (!isset($BETA_CONSTANTS['Privileges'][$acc])) return false; //means they have no privs
	if ($priv == '') return true; //means they have some kind of priv
	if(!isset($PRIVILEGES[$priv]))
		return false;
	if (in_array($PRIVILEGES[$priv], $BETA_CONSTANTS['Privileges'][$acc])) return true; //specific priv allowed
	return false;
}

function has_privilege($priv = '', $acc = 0)
{
	//PAGE
	return false;
	//if ($priv == 'Always Join Alliances') return TRUE;
	//if ($priv == 'See Location') return TRUE;
	//if ($priv == 'Money Doesn\'t Matter') return TRUE;
	global $ADMINS, $PRIVILEGES, $ACCOUNT_ID;
	if ($acc == 0) $acc = $ACCOUNT_ID;
	if(has_beta_privilege($priv,$acc))
		return true;
	if (!isset($ADMINS[$acc])) return FALSE; //means they have no privs
	if ($priv == '') return TRUE; //means they have some kind of priv
	if (in_array($PRIVILEGES[$priv], $ADMINS[$acc])) return TRUE; //specific priv allowed
	return FALSE;
}

function create_echo_error($message) {

	$return=('<h1>ERROR</h1>');
	$return.=('<p><b><big>'.$message.'</big></b></p>');
	$return.=('<br><br><br>');
	$return.=('<p><small>If the error was caused by something you entered, press back and try again.</small></p>');
	$return.=('<p><small>If it was a DB Error, press back and try again, or logoff and log back on.</small></p>');
	$return.=('<p><small>If the error was unrecognizable, please notify the administrators.</small></p>');
}

function create_menue($menue) {

	$return = '<div class="bar1"><div><span class="nowrap">';
	foreach ($menue as $number => $entry) {
		if ($number > 0) $return .= '</span> | <span class="nowrap">';
		$return .= $entry;
	}
	$return .= '</span></div></div><br />';
	return $return;
}

function create_topic($topic) {
	return '<h1>' . strtoupper($topic) . '</h1><br />';
}

function create_table() {
 	return '<table class="standard" cellspacing="0">';
}

function create_echo_form($container) {
	return create_form_name($container, 'FORM');
}

function create_form_name($container, $name) {
	return create_form_parameter($container, 'name="'.$name.'"');
}

function create_form_parameter($container, $param) {
	return '<form '.$param.' method="POST" action="loader.php">' .
		'<input type="hidden" name="sn" value="' . SmrSession::get_new_sn($container) . '">';
}

function create_header_link($container, $text) {
	return '<a class="header" href="loader.php?sn=' . SmrSession::get_new_sn($container) . '">' . $text . '</a>';
}



function create_button($container,$text) {
	return '<div class="buttonA"><a class="buttonA" href="loader.php?sn=' . SmrSession::get_new_sn($container). '">&nbsp;' . $text . '&nbsp;</a></div>';
}

function create_form($container,$actions) {
	static $form_id = 0;
	$form = array();
	$form['form'] = '<form class="standard" id="FORM' . $form_id . '" method="POST" action="loader.php?sn=' . SmrSession::get_new_sn($container) . '">';

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

function create_submit($value) {
	return ('<input type="submit" name="action" value="'.$value.'" id="InputFields">');
}

function create_submit_style($value, $style) {
	return ('<input type="submit" name="action" value="'.$value.'" id="InputFields" style="$style">');
}

function get_file_loc($file_name, $exists = 1)
{
	global $includes, $g_id;
	static $stored_dir;
	if(isset($stored_dir))
	{
		$game_dir = $stored_dir;
	}
	else
	{
		if (SmrSession::$game_id > 0 || $g_id > 0)
		{
			if ($g_id > 0) $game_id = $g_id;
			else $g_id = SmrSession::$game_id;
			$includes->query('SELECT game_type FROM game WHERE game_id = '.$g_id.' LIMIT 1	');
			$includes->next_record();
			$stored_dir = $includes->f('game_type') . '/';
			$game_dir = $stored_dir;
		} else $game_dir = 'Old_School/';
	}
	
//PAGE
	if (file_exists(LIB . 'page/' . $file_name)) return LIB . 'page/' . $file_name;
	elseif (file_exists(ENGINE . $game_dir . $file_name)) return ENGINE . $game_dir . $file_name;
	elseif (file_exists(ENGINE . 'Old_School/' . $file_name)) return ENGINE . 'Old_School/' . $file_name;
	elseif (file_exists(LIB . $game_dir . $file_name)) return LIB . $game_dir . $file_name;
	elseif (file_exists(LIB . 'Old_School/' . $file_name)) return LIB . 'Old_School/' . $file_name;
	elseif (file_exists(ADMIN . $game_dir . $file_name)) return ADMIN . $game_dir . $file_name;
	elseif (file_exists(ADMIN . 'Old_School/' . $file_name)) return ADMIN . 'Old_School/' . $file_name;
	elseif (file_exists(WWW . $file_name)) return WWW . $file_name;
	elseif ($exists == 1) return ENGINE . 'empty.php';
	else return $file_name;

}

function get_template_loc($file_name, $exists = 1) {

	global $includes, $g_id;
	static $stored_dir;
	if(isset($stored_dir)) {
		$game_dir = $stored_dir;
	}
	else {
		if (SmrSession::$game_id > 0 || $g_id > 0) {
			if ($g_id > 0) $game_id = $g_id;
			else $g_id = SmrSession::$game_id;
			$includes->query('SELECT game_type FROM game WHERE game_id = '.$g_id.' LIMIT 1	');
			$includes->next_record();
			$stored_dir = $includes->f('game_type') . '/';
			$game_dir = $stored_dir;
		} else $game_dir = 'Old_School/';
	}
	$file_name .= '.tpl';
	if (file_exists(SMARTY_TEMPLATES_DIR . 'engine/' . $game_dir . $file_name)) return SMARTY_TEMPLATES_DIR . 'engine/' . $game_dir . $file_name;
	elseif (file_exists(SMARTY_TEMPLATES_DIR . 'engine/' . 'Old_School/' . $file_name)) return SMARTY_TEMPLATES_DIR . 'engine/' . 'Old_School/' . $file_name;
	elseif (file_exists(SMARTY_TEMPLATES_DIR . 'admin/' . $game_dir . $file_name)) return SMARTY_TEMPLATES_DIR . 'admin/' . $game_dir . $file_name;
	elseif (file_exists(SMARTY_TEMPLATES_DIR . 'admin/' . 'Old_School/' . $file_name)) return SMARTY_TEMPLATES_DIR . 'admin/' . 'Old_School/' . $file_name;
	elseif (file_exists(SMARTY_TEMPLATES_DIR .  $file_name)) return SMARTY_TEMPLATES_DIR . $file_name;
//	elseif ($exists == 1) return ENGINE . 'empty.php';
	else return 'default.tpl';//return $file_name;

}

function create_error($message) {

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'error.php';
	$container['message'] = $message;
	forward($container);
	
	/*
	global $player;
	if (empty($message) || $message = '') $message = 'File not found';
	$container=array();
	$container['url'] = 'skeleton.php';
	if ($player->isLandedOnPlanet()) $container['body'] = 'planet_main.php';
	else $container['body'] = 'current_sector.php';
	$errorMsg = '<span class="red bold">ERROR:</span> ' . $message . '!';
	$container['errorMsg'] = '.$this->escapeString($errorMsg';
	forward($container);*/

}

function forward($new_container)
{
	global $URL, $time_start,$sn,$container,$var;

	// add start time of that script to next page
	$new_container['time'] = $time_start;
	// this sn identifies our container later

	SmrSession::$var = array();
	SmrSession::$var[$sn] = $new_container;

	SmrSession::update();
	$var = $new_container;
	$container = $new_container;

	do_voodoo();

}

function forwardURL($new_container)
{

	global $URL, $time_start,$sn,$container,$var;

	// add start time of that script to next page
	$new_container['time'] = $time_start;
	// this sn identifies our container later

//BACKBUTTON	
	SmrSession::$var = array();
	SmrSession::$var[$sn] = $new_container;

	SmrSession::update();
	$var = $new_container;
	$container = $new_container;

	require_once(get_file_loc($var['url']));

}

function transfer($what) {

	global $var, $container;

	// transfer this value to next container
	$container[$what] = $var[$what];

}

function create_container($file, $body) {

	$container = array();
	$container['url'] = $file;
	$container['body'] = $body;

	return $container;

}

//function format_string($db_str, $transfer_html=false) {
//
//	if ($transfer_html)
//		return '\'' . mysql_escape_string(htmlentities($db_str)) . '\'';
//	else
//		return '\'' . mysql_escape_string($db_str) . '\'';
//
//}

function create_link($container, $text) {
	return '<a href="loader.php?sn=' . SmrSession::get_new_sn($container) . '">' . $text . '</a>';
}

function get_colored_text($value, $text, $type='Game', $return_type = 'Normal')
{

	if ($type == 'Game')
	{
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
		$colour = $r_component . $g_component . '00';
		if ($return_type == 'Color') return $colour;
		return '<span style="color:#' . $colour . '">' . $text . '</span>';
	}
	elseif ($type == 'IRC')
	{
		//IRC color codes
		if ($value >= 300) $colour = '[k03]';
		elseif ($value <= -300) $colour = '[k04]';
		else $colour = '[k08]';
		if ($return_type == 'Color') return $colour;
		return $colour . $text;
	}
}

function makeBold($value) {

	if ($value)
		return ' style="font-weight:bold;"';
	else
		return '';

}

function get_form_value($name) {

	global $_POST;

	if (!empty($_POST[$name]))
		return $_POST[$name];
	else
		return 0;

}

function different_level($rank1, $rank2, $forced_vet1, $forced_vet2) {

	return false;
	// we are newbie, he vet
	if ($rank1 < FLEDGLING && $rank2 > BEGINNER && $forced_vet1 == 'FALSE')
		return true;

	// we are vet, he newbie
	if ($rank1 > BEGINNER && $rank2 < FLEDGLING && $forced_vet2 == 'FALSE')
		return true;

	// we are a forces vet, he newbie
	if ($forced_vet1 == 'TRUE' && $rank2 < FLEDGLING && $forced_vet2 == 'FALSE')
		return true;

	// we newbie, he forced vet
	if ($rank1 < FLEDGLING && $forced_vet2 == 'TRUE' && $forced_vet1 == 'FALSE')
		return true;
	return false;

}

function hex2bin( $data ) {

	$len = strlen( $data );
	return pack( 'H' . $len, $data );

}

function word_filter($string) {
	
	static $words;
	
	if(!is_array($words)) {
		$db = new SMR_DB();
		$db->query('SELECT word_value, word_replacement FROM word_filter');
		$words = array();
		while($db->next_record()) {
			$row = $db->fetch_row();
			$words[] = array('word_value' => $row['word_value'],'word_replacement'=> $row['word_replacement']);
		}
	}
	
	foreach ($words as $word) {
		$string = eregi_replace($word['word_value'],$word['word_replacement'],$string);

	}

	return $string;
	
}

?>