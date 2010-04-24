<?php

if ($account->getTotalSmrCredits() == 0)
	create_error('You don\'t have enough SMR Credits.  Donate money to SMR to gain SMR Credits!');

if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];
if ($action == 'Include HTML (2 SMR Credits)') $html = TRUE;
elseif (isset($var['html'])) $html = $var['html'];
if (isset($_REQUEST['ship_name'])) $name = $_REQUEST['ship_name'];
elseif (isset($var['ship_name'])) $name = $var['ship_name'];
$done = $var['done'];
$continue = $_REQUEST['continue'];
if (empty($html)) $continue = TRUE;
if ($action == 'Paint a logo (3 SMR Credits)')
{
	// check if we have an image
	if ($_FILES['photo']['error'] == UPLOAD_ERR_OK)
	{
		// get dimensions
		$size = getimagesize($_FILES['photo']['tmp_name']);
		// check if we really have a jpg
		if ($size[2] < 1 || $size[2] > 3)
			create_error('Only gif, jpg or png-image allowed! s = '.$size[2]);
	
		// check if width > 200
		if ($size[0] > 200)
			create_error('Image is wider than 200 pixels!');
	
		// check if height > 30
		if ($size[1] > 30)
			create_error('Image is higher than 30 pixels!');
		if (filesize($_FILES['photo']['tmp_name']) > 20560 && SmrSession::$account_id >= 100)
			create_error('Image is bigger than 20k');
		
		$orig_name = '<img style="padding: 3px 3px 3px 3px;" src="'.URL.'/upload/' . SmrSession::$account_id . 'logo"><br />';
		$cred_cost = 3;
		$account->decreaseTotalSmrCredits($cred_cost);
		$db->query('REPLACE INTO ship_has_name (game_id, account_id, ship_name) VALUES (' .
				$player->getGameID().', '.$player->getAccountID().', ' . $db->escape_string($orig_name, FALSE) . ')');
		
		move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD . SmrSession::$account_id . 'logo');
		$container=create_container('skeleton.php','bar_main.php');
		$container['script']='bar_opening.php';
		$container['message'] = '<div align="center">Your logo was successfully painted!</div><br />';
		forward($container);
	}
	else
		create_error('Error while uploading');
}

if ($action == 'Include HTML (2 SMR Credits)' && !$done)
{
	$PHP_OUTPUT.=('<div align=center>If you ship is found to use HTML inappropriatly you may be banned.');
	$PHP_OUTPUT.=('  Innappropriate HTML includes but is not limited to something that can either cause display errors or cause functionallity of the game to stop.  Also it is your responsibility to make sure ALL HTML tags that need to be closed are closed!<br />');
	$PHP_OUTPUT.=('Preview<br />' . stripslashes($name) . '<br /></div>');
	$PHP_OUTPUT.=('Are you sure you want to continue?<br />');
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'bar_main.php';
	$container['script'] = 'bar_ship_name.php';
	$container['process'] = 'yes';
	$container['html'] = TRUE;
	$container['done'] = TRUE;
	$container['ship_name'] = stripslashes($name);
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('Yes:<input type="radio" name="continue" value="TRUE"><br />No:<input type=radio name=continue value=FALSE><br />');
	$PHP_OUTPUT.=create_submit('Continue');
	$PHP_OUTPUT.=('</form>');
}
elseif (isset($var['process']) && $continue == 'TRUE')
{
	$orig_name = $name;
	if ($html)
	{
		$cred_cost = 2;
		$max_len = 128;
		$name = $db->escape_string($name);
		//check for some bad html
		$bad = array('<form','<applet','<a ','<bgsound','<body','<meta','<dd','<dir','<dl','<!doctype','<dt','<embed','<frame','<head','<hr','<iframe','<ilayer','<img','<input','<isindex','<layer','<li','<link','<map','<menu','<nobr','<no','<object','<ol','<opt','<p','<script','<select','<sound','<td','<text','<t','<ul','<h','<br','</marquee><marquee','size','width','height','<div','width=','</marquee>%<marquee','</marquee>?');
		foreach($bad as $check)
		{
			if (stristr($name, $check))
			{
				$check .= '*>';
				if ($check != '<h*>' && $check != '</marquee>?*>') create_error(htmlentities($check, ENT_NOQUOTES) . ' tag is not allowed in ship names.<br /><small>If you believe the name is appropriate please contact an admin.</small>');
				elseif ($check == '</marquee>?*>') create_error('Sorry no text is allowed to follow a ' . htmlentities('</marquee>', ENT_NOQUOTES) . ' tag.');
				else create_error('Either you used the ' . htmlentities($check, ENT_NOQUOTES) . ' tag which is not allowed or the ' . htmlentities('<html>', ENT_NOQUOTES) . ' tag which is not needed.');
			}
		}
		list ($first, $second) = explode('</marquee>', $name);
		if ($second != '')
			create_error('Sorry no text is allowed to follow a ' . htmlentities('</marquee>', ENT_NOQUOTES) . ' tag.');
		
		list ($first, $second) = explode('<marquee>', $name);
		if ($first != '' && $second != '')
			create_error('Sorry no text is allowed to come before a ' . htmlentities('<marquee>', ENT_NOQUOTES) . ' tag.');
		//lets try to see if they closed all tages
		$first = explode ('<', $name);
		foreach ($first as $second)
		{
			if ($second == '') continue;
			// the / char will be 0 and evaluate to false unless we put something at the start
			$second = '.' . $second;
			if (strpos($second, '/'))
			{
				$open -= 1;
				$close += 1;
				if ($open < 0) $ha = TRUE;
			}
			else
			{
				$real_open += 1;
				$open += 1;
			}
		}
		if ($open > 0)
			create_error('You must close all HTML tags.  (i.e a &lt;font color=red&gt tag must have a &lt;/font&gt; tag somewhere after it).<br /><small>If you think you received this message in error please contact an admin.');
		if ($close > $real_open || $ha || $open < 0)
			create_error('You can not close tags that do not exist!<br /><small>This could be an attempt at hacking if this action is seen again it will be logged</small>');
	}
	else
	{
		$max_len = 48;
		$cred_cost = 1;
		$name = $db->escape_string(htmlentities($name, ENT_NOQUOTES));
	}
	
	//list of html tags that have an auto br
	$word = array('</marquee>');
	$done = FALSE;
	foreach ($word as $bad)
	{
		if (stristr($name, $bad)) $done = TRUE;
	}
	if (!$done)	$orig_name .= '<br />';
	if (strlen($orig_name) > $max_len)
		create_error('That won\'t fit on your ship!');
	
	if ($account->getTotalSmrCredits() < $cred_cost)
		create_error('You don\'t have enough SMR Credits.  Donate money to SMR to gain SMR Credits!');

	// disallow certain ascii chars
	for ($i = 0; $i < strlen($orig_name); $i++)
		if (ord($orig_name[$i]) < 32 || ord($orig_name[$i]) > 127 || in_array(ord($orig_name[$i]), array(37,39,59,92,63,42)))
			create_error('The ship name contains invalid characters! ' . chr(ord($orig_name[$i])));
	
	$db->query('REPLACE INTO ship_has_name (game_id, account_id, ship_name) VALUES (' .
				$player->getGameID().', '.$player->getAccountID().', ' . $db->escape_string($orig_name, FALSE) . ')');
	$account->decreaseTotalSmrCredits($cred_cost);
	
	$message = '<div align=center>Thanks for your purchase! Your ship is ready!<br />';
	if ($html) $message .= 'If you ship is found to use HTML inappropriately you may be banned.  If your ship does contain inappropriate HTML talk to an admin ASAP.';
	$message .= '<br /></div>';
	$container=create_container('skeleton.php','bar_main.php');
	$container['script']='bar_opening.php';
	$container['message'] = $message;
	forward($container);
}
else
{
	$template->assign('PageTopic','Naming Your Ship');
	$PHP_OUTPUT.=('<div align="center">');
	//get bar name
	$db->query('SELECT location_name FROM location_type NATURAL JOIN location WHERE game_id = '.$player->getGameID().' AND sector_id = '.$player->getSectorID().' AND location_type.location_type_id > 800 AND location_type.location_type_id < 900');
	
	//next welcome them
	if ($db->nextRecord()) $PHP_OUTPUT.=('<div align=center>So you want to name your ship?  Great!  ' .
					'Anyone who knows anything will tell you ' . $db->getField('location_name') . ' ' .
					'is the place to get it done!<br /><br />');
					
	$PHP_OUTPUT.=('So...what do you want to name it? (max 48 text chars) (max 30 height by 200 width and 20k for logos)<br />');
	//start form
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'bar_main.php';
	$container['script'] = 'bar_ship_name.php';
	$container['process'] = 'yes';
	$PHP_OUTPUT.=create_form_parameter($container, 'name="ship_naming" enctype="multipart/form-data"');
	$PHP_OUTPUT.=('<input type="text" name="ship_name" value="Enter Name Here" id="InputFields"><br /><br />');
	//$PHP_OUTPUT.=('Include HTML? (2 SMR Credits)<input type=checkbox name=html><br />');
	$PHP_OUTPUT.=create_submit('Get It Painted! (1 SMR Credit)');
	$PHP_OUTPUT.=('<br /><br />');
	$PHP_OUTPUT.=create_submit('Include HTML (2 SMR Credits)');
	$PHP_OUTPUT.=('<br /><br />');
	$PHP_OUTPUT.=('Image: <input type="file" name="photo" accept="image/jpeg" id="InputFields" style="width:40%;">');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_submit('Paint a logo (3 SMR Credits)');
	$PHP_OUTPUT.=('</form></div>');
}

?>